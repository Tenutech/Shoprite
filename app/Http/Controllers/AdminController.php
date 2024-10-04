<?php

namespace App\Http\Controllers;

use Exeption;
use App\Models\User;
use App\Models\Race;
use App\Models\Message;
use App\Models\Vacancy;
use App\Models\Position;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\ChatTotalData;
use App\Models\ApplicantTotalData;
use App\Models\ApplicantMonthlyData;
use App\Models\Language;
use App\Services\DataService\ApplicantDataService;
use App\Services\DataService\VacancyDataService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(VacancyDataService $vacancyDataService, ApplicantDataService $applicantDataService)
    {
        $this->middleware(['auth', 'verified']);
        $this->applicantDataService = $applicantDataService;
        $this->vacancyDataService = $vacancyDataService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /*
    |--------------------------------------------------------------------------
    | Admin Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        if (view()->exists('admin/home')) {
            // Define the models that are relevant for the activity log.
            $allowedModels = [
                'App\Models\Application',
                'App\Models\Vacancy',
                'App\Models\Message',
                'App\Models\User'
            ];

            // Retrieve the ID of the currently authenticated user.
            $authUserId = Auth::id();
            // Get a list of IDs for vacancies that are associated with the authenticated user.
            $authVacancyIds = Vacancy::where('user_id', $authUserId)->pluck('id')->toArray();

            // Query the activity log, filtering for activities related to the allowed models.
            $activities = Activity::whereIn('subject_type', $allowedModels)
                ->where(function ($query) use ($authUserId, $authVacancyIds) {
                    // Filter for activities where the 'causer' (the user who performed the action) is the authenticated user,
                    // and the action is one of 'created', 'updated', or 'deleted'.
                    $query->where('causer_id', $authUserId)
                        ->whereIn('event', ['created', 'updated', 'deleted']);
                })
                ->orWhere(function ($q) use ($authUserId) {
                    // Include activities where the event is 'accessed' (e.g., a user viewed a vacancy or applicant profile),
                    // specifically for the authenticated user.
                    $q->where('event', 'accessed')
                    ->whereIn('description', ['job-overview.index', 'applicant-profile.index'])
                    ->where('causer_id', $authUserId);
                })
                ->orWhere(function ($q) use ($authUserId) {
                    // Include activities related to messages where the authenticated user is the recipient ('to_id').
                    $q->where('subject_type', 'App\Models\Message')
                    ->where('properties->attributes->to_id', $authUserId)
                    ->where('event', 'created');
                })
                ->orWhere(function ($q) use ($authVacancyIds) {
                    // Include activities related to applications connected to any of the vacancies owned by the authenticated user.
                    $q->where('subject_type', 'App\Models\Application')
                    ->whereIn('properties->attributes->vacancy_id', $authVacancyIds);
                })
                ->latest() // Order the results by the most recent first.
                ->limit(10) // Limit the results to the 10 most recent activities.
                ->get(); // Execute the query and get the results

            // Filter activities to get only those related to vacancies.
            $vacancyActivities = $activities->where('subject_type', 'App\Models\Vacancy');
            // Extract the IDs of the affected vacancies from these activities.
            $vacancyIds = $vacancyActivities->pluck('subject_id');

            // Retrieve the vacancies along with their related models like position, store's brand, store's town, and type.
            $vacanciesWithRelations = Vacancy::with(['position', 'store.brand', 'store.town', 'type'])
                                            ->whereIn('id', $vacancyIds)
                                            ->get();

            // Associate each activity with its corresponding vacancy by setting a relation.
            foreach ($vacancyActivities as $activity) {
                $activity->setRelation('subject', $vacanciesWithRelations->firstWhere('id', $activity->subject_id));
            }

            // Filter activities to get only those related to messages.
            $messageActivities = $activities->where('subject_type', 'App\Models\Message');
            // Extract the IDs of the messages from these activities.
            $ids = $messageActivities->pluck('subject_id');

            // Retrieve the messages along with their related 'to' and 'from' users.
            $messagesWithRelations = Message::with('to', 'from')->whereIn('id', $ids)->get();

            // Associate each message activity with its corresponding message by setting a relation.
            foreach ($messageActivities as $activity) {
                $activity->setRelation('subject', $messagesWithRelations->firstWhere('id', $activity->subject_id));
            }

            // Filter activities to get only those related to applications.
            $applicationActivities = $activities->where('subject_type', 'App\Models\Application');
            // Extract the IDs of the applications from these activities.
            $connectionIds = $applicationActivities->pluck('subject_id');

            // Retrieve the applications along with their related user and the user of the related vacancy.
            $applicationWithRelations = Application::with(['user', 'vacancy.user'])->whereIn('id', $connectionIds)->get();

            // Associate each application activity with its corresponding application by setting a relation.
            foreach ($applicationActivities as $activity) {
                $activity->setRelation('subject', $applicationWithRelations->firstWhere('id', $activity->subject_id));
            }

            // For activities where messages have been deleted, extract the 'to_id' from the old properties.
            $deletedMessageUserIds = $activities->where('subject_type', 'App\Models\Message')
                                                ->where('event', 'deleted')
                                                ->map(function ($activity) {
                                                    return data_get($activity, 'properties.old.to_id');
                                                })
                                                ->filter()
                                                ->unique();

            // Retrieve the users associated with the deleted messages.
            $usersForDeletedMessages = User::whereIn('id', $deletedMessageUserIds)->get();

            // Filter activities to get only those where messages have been deleted.
            $deletedMessageActivities = $activities->where('subject_type', 'App\Models\Message')->where('event', 'deleted');

            // Associate each deleted message activity with the user it was sent to by setting a relation.
            foreach ($deletedMessageActivities as $activity) {
                $toId = data_get($activity, 'properties.old.to_id');
                $activity->setRelation('userForDeletedMessage', $usersForDeletedMessages->firstWhere('id', $toId));
            }

            // Extract the encrypted IDs from the URL of the 'accessed' activities for vacancies.
            $accessedVacancyEncryptedIds = $activities->where('event', 'accessed')
            ->where('description', 'job-overview.index')
            ->map(function ($activity) {
                // Get the URL from the activity's properties.
                return data_get($activity, 'properties.url');
            })
            ->map(function ($url) {
                // Split the URL into segments and get the last segment, which is the encrypted ID.
                $segments = explode('/', $url);
                $encryptedId = count($segments) > 1 ? last($segments) : null;

                // Attempt to decrypt the encrypted ID.
                if ($encryptedId) {
                    try {
                        return Crypt::decryptString($encryptedId);
                    } catch (\Exception $e) {
                        // If decryption fails, return null.
                        return null;
                    }
                }
                return null;
            })
            ->filter(); // Remove any null values from the collection.

            // Retrieve the vacancies that have been accessed using the decrypted IDs.
            $accessedOpportunities = Vacancy::whereIn('id', $accessedVacancyEncryptedIds)->get();

            // Filter the activities to get only those with 'accessed' event and 'job-overview.index' description.
            $accessedVacancyActivities = $activities->where('event', 'accessed')->where('description', 'job-overview.index');

            // Associate each accessed vacancy activity with the corresponding vacancy.
            foreach ($accessedVacancyActivities as $activity) {
                // Decrypt the encrypted ID from the URL.
                $encryptedId = last(explode('/', data_get($activity, 'properties.url')));
                try {
                    $decryptedId = Crypt::decryptString($encryptedId);
                    // Find the vacancy using the decrypted ID and set the relation.
                    $opportunity = $accessedOpportunities->firstWhere('id', $decryptedId);
                    $activity->setRelation('accessedVacancy', $opportunity);
                } catch (\Exception $e) {
                    // If decryption fails, skip this iteration.
                    continue;
                }
            }

            // Extract the encrypted IDs from the URL of the 'accessed' activities for applicants.
            $accessedApplicantEncryptedIds = $activities->where('event', 'accessed')
            ->where('description', 'applicant-profile.index')
            ->map(function ($activity) {
                // Parse the URL to get the query string, then extract the 'id' parameter.
                parse_str(parse_url(data_get($activity, 'properties.url'), PHP_URL_QUERY), $queryParams);
                return $queryParams['id'] ?? null;
            })
            ->map(function ($encryptedId) {
                // Attempt to decrypt the encrypted ID.
                if ($encryptedId) {
                    try {
                        return Crypt::decryptString($encryptedId);
                    } catch (\Exception $e) {
                        // If decryption fails, return null.
                        return null;
                    }
                }
                return null;
            })
            ->filter(); // Remove any null values from the collection.

            // Retrieve the applicants that have been accessed using the decrypted IDs.
            $accessedApplicants = Applicant::whereIn('id', $accessedApplicantEncryptedIds)->get();

            // Filter the activities to get only those with 'accessed' event and 'applicant-profile.index' description.
            $accessedApplicantActivities = $activities->where('event', 'accessed')->where('description', 'applicant-profile.index');

            // Associate each accessed applicant activity with the corresponding applicant.
            foreach ($accessedApplicantActivities as $activity) {
                // Parse the URL to get the encrypted ID from the query string.
                parse_str(parse_url(data_get($activity, 'properties.url'), PHP_URL_QUERY), $queryParams);
                $encryptedId = $queryParams['id'] ?? null;
                if ($encryptedId) {
                    try {
                        // Decrypt the encrypted ID and find the corresponding applicant.
                        $decryptedId = Crypt::decryptString($encryptedId);
                        $applicant = $accessedApplicants->firstWhere('id', $decryptedId);
                        // Set the relation on the activity.
                        $activity->setRelation('accessedApplicant', $applicant);
                    } catch (\Exception $e) {
                        // If decryption fails or the applicant is not found, skip this iteration.
                        continue;
                    }
                }
            }

            //Positions
            $positions = Position::withCount('users')
                ->whereNotIn('id', [1, 10])
                ->orderBy('users_count', 'desc')
                ->take(10)
                ->get();

            //Current Year
            $currentYear = now()->year;
            $currentMonth = now()->month - 1;
            $previousYear = $currentYear - 1;

            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            // Determine months to query for the current year
            $queryMonthsCurrentYear = array_slice($months, 0, $currentMonth + 1);
            // Determine months to query for the previous year, excluding months overlapping with the current year
            $queryMonthsPreviousYear = array_slice($months, $currentMonth + 1);

            $currentYearData = ApplicantTotalData::where('year', $currentYear)->first();
            $previousYearData = ApplicantTotalData::where('year', $currentYear - 1)->first();
            $currentYearId = $currentYearData->id;
            $previousYearId = $previousYearData->id;

            $applicantsPerProvince = [];
            $applicantsByRace = [];
            $totalApplicantsPerMonth = [];
            $applicationsPerMonth = [];
            $interviewedPerMonth = [];
            $appointedPerMonth = [];
            $rejectedPerMonth = [];
            $percentMovementApplicationsPerMonth = 0;
            $percentMovementInterviewedPerMonth = 0;
            $percentMovementAppointedPerMonth = 0;
            $percentMovementRejectedPerMonth = 0;

            if ($currentYearData) {
                // Fetch total applicants per province for the current year using the relation
                $applicantsPerProvince = $currentYearData->monthlyData()
                ->where('category_type', 'Province')
                ->join('provinces', 'applicant_monthly_data.category_id', '=', 'provinces.id')
                ->select('provinces.name', DB::raw('SUM(applicant_monthly_data.count) as total_applicants'))
                ->groupBy('provinces.name')
                ->get()
                ->map(function ($item) {
                    // Format for the chart
                    return ['x' => $item->name, 'y' => (int) $item->total_applicants];
                })
                ->toArray();

                // Fetch applicants by race for the current year
                $applicantsByRaceCurrentYear = ApplicantMonthlyData::join('races', 'applicant_monthly_data.category_id', '=', 'races.id')
                ->where('applicant_total_data_id', $currentYearId)
                ->whereIn('month', $queryMonthsCurrentYear)
                ->where('category_type', 'Race')
                ->get(['races.name as race_name', 'applicant_monthly_data.month', 'applicant_monthly_data.count']);

                // Fetch applicants by race for the previous year
                $applicantsByRacePreviousYear = ApplicantMonthlyData::join('races', 'applicant_monthly_data.category_id', '=', 'races.id')
                ->where('applicant_total_data_id', $previousYearId)
                ->whereIn('month', $queryMonthsPreviousYear)
                ->where('category_type', 'Race')
                ->get(['races.name as race_name', 'applicant_monthly_data.month', 'applicant_monthly_data.count']);

                // Combine both year's data
                $combinedApplicantsByRace = $applicantsByRacePreviousYear->concat($applicantsByRaceCurrentYear);

                // Initialize the series array for the chart
                $combinedApplicantsByRace->groupBy('race_name')->each(function ($items, $raceName) use (&$applicantsByRace, $months, $currentMonth) {
                    $dataPoints = array_fill(0, count($months), 0); // Initialize with zeros for all months

                    foreach ($items as $item) {
                        $index = array_search($item->month, $months);
                        if ($index !== false) {
                            $dataPoints[$index] = (int) $item->count;
                        }
                    }

                    // Adjust for the current year by slicing and rearranging months to start from the current month backwards
                    $dataPoints = array_merge(array_slice($dataPoints, $currentMonth + 1), array_slice($dataPoints, 0, $currentMonth + 1));

                    $applicantsByRace[] = [
                        'name' => $raceName,
                        'data' => array_reverse($dataPoints), // Reverse to start with the most recent month
                    ];
                });

                // Handle the previous year's data
                if ($currentMonth < 11) {
                    foreach (array_slice($months, $currentMonth + 1) as $month) {
                        $monthKey = strtolower($month); // Convert month to the key format (e.g., 'jan', 'feb')
                        $totalApplicantsPerMonth[] = $previousYearData->$monthKey ?? 0;
                    }
                }

                // Add data for the current year up to the current month
                foreach (array_slice($months, 0, $currentMonth + 1) as $month) {
                    $monthKey = strtolower($month);
                    $totalApplicantsPerMonth[] = $currentYearData->$monthKey ?? 0;
                }

                // Fetch application data for the current year
                $applicationsCurrentYear = ApplicantMonthlyData::where('category_type', 'Application')
                ->where('applicant_total_data_id', $currentYearId)
                ->whereIn('month', $queryMonthsCurrentYear)
                ->get(['month', 'count']);

                // Fetch application data for the previous year
                $applicationsPreviousYear = ApplicantMonthlyData::where('category_type', 'Application')
                ->where('applicant_total_data_id', $previousYearId)
                ->whereIn('month', $queryMonthsPreviousYear)
                ->get(['month', 'count']);

                // Combine both year's data
                $combinedApplications = $applicationsPreviousYear->concat($applicationsCurrentYear);

                // Process the combined data for visualization
                $combinedApplications->groupBy('month')->each(function ($items, $month) use (&$applicationsPerMonth, $months) {
                    $totalApplications = $items->sum('count'); // Sum up applications for the month

                    $index = array_search($month, $months);
                    if ($index !== false) {
                        $applicationsPerMonth[] = $totalApplications;
                    }
                });

                //Percent movement from last month
                $sumOfApplications = array_sum($applicationsPerMonth);

                if (count($applicationsPerMonth) >= 2) {
                    $lastMonthApplications = end($applicationsPerMonth);
                    $penultimateMonthApplications = $applicationsPerMonth[count($applicationsPerMonth) - 2];

                    if ($sumOfApplications > 0) {
                        $percentMovementApplicationsPerMonth = round((($lastMonthApplications - $penultimateMonthApplications) / $sumOfApplications) * 100, 2);
                    } else {
                        $percentMovementApplicationsPerMonth = 0;
                    }
                } else {
                    $percentMovementApplicationsPerMonth = 0;
                }

                // Fetch interview data for the current year
                $interviewsCurrentYear = ApplicantMonthlyData::where('category_type', 'Interviewed')
                ->where('applicant_total_data_id', $currentYearId)
                ->whereIn('month', $queryMonthsCurrentYear)
                ->get(['month', 'count']);

                // Fetch interview data for the previous year
                $interviewsPreviousYear = ApplicantMonthlyData::where('category_type', 'Interviewed')
                ->where('applicant_total_data_id', $previousYearId)
                ->whereIn('month', $queryMonthsPreviousYear)
                ->get(['month', 'count']);

                // Combine both year's data
                $combinedInterviews = $interviewsPreviousYear->concat($interviewsCurrentYear);

                // Process the combined data for visualization
                $combinedInterviews->groupBy('month')->each(function ($items, $month) use (&$interviewedPerMonth, $months) {
                    $totalInterviews = $items->sum('count'); // Sum up interviews for the month

                    $index = array_search($month, $months);
                    if ($index !== false) {
                        $interviewedPerMonth[] = $totalInterviews;
                    }
                });

                //Percent movement from last month
                $sumOfInterviewed = array_sum($interviewedPerMonth);

                if (count($interviewedPerMonth) >= 2) {
                    $lastMonthInterviewed = end($interviewedPerMonth);
                    $penultimateMonthInterviewed = $interviewedPerMonth[count($interviewedPerMonth) - 2];

                    if ($sumOfInterviewed > 0) {
                        $percentMovementInterviewedPerMonth = round((($lastMonthInterviewed - $penultimateMonthInterviewed) / $sumOfInterviewed) * 100, 2);
                    } else {
                        $percentMovementInterviewedPerMonth = 0;
                    }
                } else {
                    $percentMovementInterviewedPerMonth = 0;
                }

                // Fetch appointed data for the current year
                $appointedCurrentYear = ApplicantMonthlyData::where('category_type', 'Appointed')
                ->where('applicant_total_data_id', $currentYearId)
                ->whereIn('month', $queryMonthsCurrentYear)
                ->get(['month', 'count']);

                // Fetch appointed data for the previous year
                $appointedPreviousYear = ApplicantMonthlyData::where('category_type', 'Appointed')
                ->where('applicant_total_data_id', $previousYearId)
                ->whereIn('month', $queryMonthsPreviousYear)
                ->get(['month', 'count']);

                // Combine both year's data
                $combinedAppointed = $appointedPreviousYear->concat($appointedCurrentYear);

                // Process the combined data for visualization
                $combinedAppointed->groupBy('month')->each(function ($items, $month) use (&$appointedPerMonth, $months) {
                    $totalAppointed = $items->sum('count'); // Sum up appointed for the month

                    $index = array_search($month, $months);
                    if ($index !== false) {
                        $appointedPerMonth[] = $totalAppointed;
                    }
                });

                //Percent movement from last month
                $sumOfAppointed = array_sum($appointedPerMonth);

                if (count($appointedPerMonth) >= 2) {
                    $lastMonthAppointed = end($appointedPerMonth);
                    $penultimateMonthAppointed = $appointedPerMonth[count($appointedPerMonth) - 2];

                    if ($sumOfAppointed > 0) {
                        $percentMovementAppointedPerMonth = round((($lastMonthAppointed - $penultimateMonthAppointed) / $sumOfAppointed) * 100, 2);
                    } else {
                        $percentMovementAppointedPerMonth = 0;
                    }
                } else {
                    $percentMovementAppointedPerMonth = 0;
                }

                // Fetch rejected data for the current year
                $rejectedCurrentYear = ApplicantMonthlyData::where('category_type', 'Rejected')
                ->where('applicant_total_data_id', $currentYearId)
                ->whereIn('month', $queryMonthsCurrentYear)
                ->get(['month', 'count']);

                // Fetch rejected data for the previous year
                $rejectedPreviousYear = ApplicantMonthlyData::where('category_type', 'Rejected')
                ->where('applicant_total_data_id', $previousYearId)
                ->whereIn('month', $queryMonthsPreviousYear)
                ->get(['month', 'count']);

                // Combine both year's data
                $combinedRejected = $rejectedPreviousYear->concat($rejectedCurrentYear);

                // Process the combined data for visualization
                $combinedRejected->groupBy('month')->each(function ($items, $month) use (&$rejectedPerMonth, $months) {
                    $totalRejected = $items->sum('count'); // Sum up rejected for the month

                    $index = array_search($month, $months);
                    if ($index !== false) {
                        $rejectedPerMonth[] = $totalRejected;
                    }
                });

                //Percent movement from last month
                $sumOfRejected = array_sum($rejectedPerMonth);

                if (count($rejectedPerMonth) >= 2) {
                    $lastMonthRejected = end($rejectedPerMonth);
                    $penultimateMonthRejected = $rejectedPerMonth[count($rejectedPerMonth) - 2];

                    if ($sumOfRejected > 0) {
                        $percentMovementRejectedPerMonth = round((($lastMonthRejected - $penultimateMonthRejected) / $sumOfRejected) * 100, 2);
                    } else {
                        $percentMovementRejectedPerMonth = 0;
                    }
                } else {
                    $percentMovementRejectedPerMonth = 0;
                }
            }

            //Message Data
            $currentYearChatData = ChatTotalData::where('year', $currentYear)->first();
            $previousYearChatData = ChatTotalData::where('year', $previousYear)->first();

            $totalIncomingMessages = $currentYearChatData->total_incoming + $previousYearChatData->total_incoming;
            $totalOutgoingMessages = $currentYearChatData->total_outgoing + $previousYearChatData->total_outgoing;

            $incomingMessages = [];
            $outgoingMessages = [];

            if ($currentYearData) {
                // Handle data from the previous year, if current month is not January
                if ($currentMonth < 11) {
                    foreach (array_slice($months, $currentMonth + 1) as $month) {
                        $monthIncoming = strtolower($month) . '_incoming';
                        $monthOutgoing = strtolower($month) . '_outgoing';
                        $incomingMessages[] = $previousYearChatData->$monthIncoming ?? 0;
                        $outgoingMessages[] = $previousYearChatData->$monthOutgoing ?? 0;
                    }
                }

                // Add data for the current year, including the current month
                foreach (array_slice($months, 0, $currentMonth + 1) as $month) {
                    $monthIncoming = strtolower($month) . '_incoming';
                    $monthOutgoing = strtolower($month) . '_outgoing';
                    $incomingMessages[] = $currentYearChatData->$monthIncoming ?? 0;
                    $outgoingMessages[] = $currentYearChatData->$monthOutgoing ?? 0;
                }
            }

            // Fetch applicants positions
            $positionsTotals = ApplicantMonthlyData::join('positions', 'applicant_monthly_data.category_id', '=', 'positions.id')
            ->select('positions.name as positionName', DB::raw('SUM(applicant_monthly_data.count) as total'))
            ->where('applicant_monthly_data.category_type', 'Position')
            ->groupBy('positions.name')
            ->get();

            $applicantsByPosition = $positionsTotals->map(function ($item) {
                return [
                    'name' => $item->positionName,
                    'data' => [$item->total]
                ];
            })->all();

            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();

            $averageShortlistTime = $this->vacancyDataService->getNationwideAverageTimeToShortlist();
            $averageTimeToHire = $this->vacancyDataService->getNationwideAverageTimeToHire();
            $adoptionRate = $this->vacancyDataService->getNationwideVacancyFillRate($startDate, $endDate);
            $applicationCompletionRate = $this->applicantDataService->getApplicationCompletionRate($startDate, $endDate);
            $dropOffRates = $this->applicantDataService->getDropOffRates($startDate, $endDate);
            // $completionByRegion = $this->applicantDataService->getCompletionByRegion($startDate, $endDate);

            return view('admin/home', [
                'activities' => $activities,
                'positions' => $positions,
                'currentYearData' => $currentYearData,
                'previousYearData' => $previousYearData,
                'applicantsPerProvince' => $applicantsPerProvince,
                'applicantsByRace' => $applicantsByRace,
                'totalApplicantsPerMonth' => $totalApplicantsPerMonth,
                'totalIncomingMessages' => $totalIncomingMessages,
                'totalOutgoingMessages' => $totalOutgoingMessages,
                'incomingMessages' => $incomingMessages,
                'outgoingMessages' => $outgoingMessages,
                'applicantsByPosition' => $applicantsByPosition,
                'applicationsPerMonth' => $applicationsPerMonth,
                'interviewedPerMonth' => $interviewedPerMonth,
                'appointedPerMonth' => $appointedPerMonth,
                'rejectedPerMonth' => $rejectedPerMonth,
                'percentMovementApplicationsPerMonth' => $percentMovementApplicationsPerMonth,
                'percentMovementInterviewedPerMonth' => $percentMovementInterviewedPerMonth,
                'percentMovementAppointedPerMonth' => $percentMovementAppointedPerMonth,
                'percentMovementRejectedPerMonth' => $percentMovementRejectedPerMonth,
                'averageShortlistTime' => $averageShortlistTime,
                'averageTimeToHire' => $averageTimeToHire,
                'adoptionRate' => $adoptionRate,
                'applicationCompletionRate' => $applicationCompletionRate,
                'dropOffRates' => $dropOffRates,
                // 'completionByRegion' => $completionByRegion,
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Update Data
    |--------------------------------------------------------------------------
    */

    public function updateData(Request $request)
    {
        // Validate the request data
        $request->validate([
            'start_date' => 'required|date_format:d/m/Y',
            'end_date' => 'required|date_format:d/m/Y',
        ]);

        try {
            // Convert validated dates to Carbon instances
            $startDate = Carbon::createFromFormat('d/m/Y', $request['start_date']);
            $endDate = Carbon::createFromFormat('d/m/Y', $request['end_date']);

            // Extract the year and month for range processing
            $startYear = $startDate->year;
            $endYear = $endDate->year;
            $startMonth = $startDate->month;
            $endMonth = $endDate->month;

            // Extract the year and month for range processing
            $startYear = $startDate->year;
            $endYear = $endDate->year;
            $startMonth = $startDate->format('M');
            $endMonth = $endDate->format('M');

            // Prepare an array to map month names to their numeric values
            $months = [
                'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6, 'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12,
            ];

            $monthlyCounts = [];
            $totalApplicants = 0;
            $totalAppointed = 0;
            $applicantsPerProvince = [];
            $applicantsByRace = [];
            $totalApplicantsPerMonth = [];
            $applicationsPerMonth = [];
            $interviewedPerMonth = [];
            $appointedPerMonth = [];
            $rejectedPerMonth = [];
            $percentMovementApplicationsPerMonth = 0;
            $percentMovementInterviewedPerMonth = 0;
            $percentMovementAppointedPerMonth = 0;
            $percentMovementRejectedPerMonth = 0;
            $totalIncomingMessages = 0;
            $totalOutgoingMessages = 0;
            $incomingMessages = [];
            $outgoingMessages = [];
            $applicantsByPosition = [];
            $totalTimeToHire = 0;
            $totalAbsorptionRate = 0;

            // Fetch the applicants per province for the selected date range
            $applicantsPerProvince = DB::table('applicant_monthly_data')
            ->join('applicant_total_data', 'applicant_monthly_data.applicant_total_data_id', '=', 'applicant_total_data.id')
            ->join('provinces', 'applicant_monthly_data.category_id', '=', 'provinces.id')
            ->where('applicant_monthly_data.category_type', 'Province')
            ->where(function ($query) use ($startYear, $endYear, $startMonth, $endMonth, $months) {
                if ($startYear == $endYear) {
                    $query->whereYear('applicant_total_data.year', $startYear)
                        ->where(function ($subQuery) use ($startMonth, $endMonth, $months) {
                            $subQuery->whereIn('applicant_monthly_data.month', array_keys(array_filter($months, function ($value) use ($startMonth, $endMonth, $months) {
                                return $value >= $months[$startMonth] && $value <= $months[$endMonth];
                            })));
                        });
                } else {
                    $query->where(function ($subQuery) use ($startYear, $startMonth, $months) {
                        $subQuery->whereYear('applicant_total_data.year', $startYear)
                                ->whereIn('applicant_monthly_data.month', array_keys(array_filter($months, function ($value) use ($startMonth, $months) {
                                    return $value >= $months[$startMonth];
                                })));
                    })->orWhere(function ($subQuery) use ($endYear, $endMonth, $months) {
                        $subQuery->whereYear('applicant_total_data.year', $endYear)
                                ->whereIn('applicant_monthly_data.month', array_keys(array_filter($months, function ($value) use ($endMonth, $months) {
                                    return $value <= $months[$endMonth];
                                })));
                    })->orWhereBetween('applicant_total_data.year', [$startYear + 1, $endYear - 1]);
                }
            })
            ->select('provinces.name', DB::raw('SUM(applicant_monthly_data.count) as total_applicants'))
            ->groupBy('provinces.name')
            ->get()
            ->map(function ($item) {
                // Format for the chart
                return ['x' => $item->name, 'y' => (int) $item->total_applicants];
            })
            ->toArray();

            // Fetch applicants by race for the selected date range
            $applicantsByRace = DB::table('applicant_monthly_data')
            ->join('applicant_total_data', 'applicant_monthly_data.applicant_total_data_id', '=', 'applicant_total_data.id')
            ->join('races', 'applicant_monthly_data.category_id', '=', 'races.id')
            ->where('applicant_monthly_data.category_type', 'Race')
            ->where(function ($query) use ($startYear, $endYear, $startMonth, $endMonth, $months) {
                if ($startYear == $endYear) {
                    $query->whereYear('applicant_total_data.year', $startYear)
                        ->whereIn('applicant_monthly_data.month', array_keys(array_filter($months, function ($value) use ($startMonth, $endMonth, $months) {
                            return $value >= $months[$startMonth] && $value <= $months[$endMonth];
                        })));
                } else {
                    $query->where(function ($subQuery) use ($startYear, $startMonth, $months) {
                        $subQuery->whereYear('applicant_total_data.year', $startYear)
                            ->whereIn('applicant_monthly_data.month', array_keys(array_filter($months, function ($value) use ($startMonth, $months) {
                                return $value >= $months[$startMonth];
                            })));
                    })->orWhere(function ($subQuery) use ($endYear, $endMonth, $months) {
                        $subQuery->whereYear('applicant_total_data.year', $endYear)
                            ->whereIn('applicant_monthly_data.month', array_keys(array_filter($months, function ($value) use ($endMonth, $months) {
                                return $value <= $months[$endMonth];
                            })));
                    })->orWhereBetween('applicant_total_data.year', [$startYear + 1, $endYear - 1]);
                }
            })
            ->select('races.name as race_name', 'applicant_monthly_data.month', 'applicant_monthly_data.count')
            ->get();

            // Group and format the data for the chart
            $applicantsByRace = $applicantsByRace->groupBy('race_name')->map(function ($items, $raceName) {
                $formattedData = $items->map(function ($item) {
                    return $item->month . ': ' . (int)$item->count;
                });
                return [
                    'name' => $raceName,
                    'data' => $formattedData->toArray(),
                ];
            })->values()->toArray();

            //Total application per month
            // Loop through each year in the range
            for ($year = $startYear; $year <= $endYear; $year++) {
                $yearData = ApplicantTotalData::where('year', $year)->first();
                if (!$yearData) {
                    continue;
                }

                // Determine the months to process for the current year
                if ($year == $startYear && $year == $endYear) {
                    // If start year and end year are the same, process from start month to end month
                    $monthsToProcess = array_keys(array_filter($months, function ($value) use ($startMonth, $endMonth, $months) {
                        return $value >= $months[$startMonth] && $value <= $months[$endMonth];
                    }));
                } elseif ($year == $startYear) {
                    // For the start year, process from the start month to December
                    $monthsToProcess = array_keys(array_filter($months, function ($value) use ($startMonth, $months) {
                        return $value >= $months[$startMonth];
                    }));
                } elseif ($year == $endYear) {
                    // For the end year, process from January to the end month
                    $monthsToProcess = array_keys(array_filter($months, function ($value) use ($endMonth, $months) {
                        return $value <= $months[$endMonth];
                    }));
                } else {
                    // For the intermediate years, process all months
                    $monthsToProcess = array_keys($months);
                }

                // Add data for the current year's months
                foreach ($monthsToProcess as $month) {
                    $monthKey = strtolower($month); // Convert month to the key format (e.g., 'jan', 'feb')
                    $count = $yearData->$monthKey ?? 0;
                    $totalApplicantsPerMonth[] = $month . ' \'' . substr($year, -2) . ': ' . $count;
                    $monthlyCounts[] = $count;
                    $totalApplicants += $count; +

                    // Fetch count for "Application"
                    $applicationsCount = DB::table('applicant_monthly_data')
                    ->join('applicant_total_data', 'applicant_monthly_data.applicant_total_data_id', '=', 'applicant_total_data.id')
                    ->where('applicant_monthly_data.category_type', 'Application')
                    ->where('applicant_total_data.year', $year)
                    ->where('applicant_monthly_data.month', $month)
                    ->sum('applicant_monthly_data.count');

                    // If no record is found, set the count to 0
                    $applicationsCount = $applicationsCount ?: 0;

                    $applicationsPerMonth[] = $month . ' \'' . substr($year, -2) . ': ' . $applicationsCount;
                    $applicationsMonthlyCounts[] = $applicationsCount;

                    // Fetch count for "Interviewed"
                    $interviewedCount = DB::table('applicant_monthly_data')
                    ->join('applicant_total_data', 'applicant_monthly_data.applicant_total_data_id', '=', 'applicant_total_data.id')
                    ->where('applicant_monthly_data.category_type', 'Interviewed')
                    ->where('applicant_total_data.year', $year)
                    ->where('applicant_monthly_data.month', $month)
                    ->sum('applicant_monthly_data.count');

                    // If no record is found, set the count to 0
                    $interviewedCount = $interviewedCount ?: 0;

                    $interviewedPerMonth[] = $month . ' \'' . substr($year, -2) . ': ' . $interviewedCount;
                    $interviewedMonthlyCounts[] = $interviewedCount;

                    // Fetch count for "Appointed"
                    $appointedCount = DB::table('applicant_monthly_data')
                    ->join('applicant_total_data', 'applicant_monthly_data.applicant_total_data_id', '=', 'applicant_total_data.id')
                    ->where('applicant_monthly_data.category_type', 'Appointed')
                    ->where('applicant_total_data.year', $year)
                    ->where('applicant_monthly_data.month', $month)
                    ->sum('applicant_monthly_data.count');

                    // If no record is found, set the count to 0
                    $appointedCount = $appointedCount ?: 0;

                    $appointedPerMonth[] = $month . ' \'' . substr($year, -2) . ': ' . $appointedCount;
                    $appointedMonthlyCounts[] = $appointedCount;

                    // Add time to total appointed
                    $totalAppointed += $appointedCount;

                    // Fetch count for "Rejected"
                    $rejectedCount = DB::table('applicant_monthly_data')
                    ->join('applicant_total_data', 'applicant_monthly_data.applicant_total_data_id', '=', 'applicant_total_data.id')
                    ->where('applicant_monthly_data.category_type', 'Rejected')
                    ->where('applicant_total_data.year', $year)
                    ->where('applicant_monthly_data.month', $month)
                    ->sum('applicant_monthly_data.count');

                    // If no record is found, set the count to 0
                    $rejectedCount = $rejectedCount ?: 0;

                    $rejectedPerMonth[] = $month . ' \'' . substr($year, -2) . ': ' . $rejectedCount;
                    $rejectedMonthlyCounts[] = $rejectedCount;

                    // Calculate Time to Hire
                    $timeToHire = DB::table('applicant_monthly_data')
                    ->join('applicant_total_data', 'applicant_monthly_data.applicant_total_data_id', '=', 'applicant_total_data.id')
                    ->where('applicant_monthly_data.category_type', 'Appointed')
                    ->where('applicant_total_data.year', $year)
                    ->where('applicant_monthly_data.month', $month)
                    ->sum('applicant_monthly_data.total_time_to_appointed');

                    // Add time to total time to hire
                    $totalTimeToHire += $timeToHire ?: 0;
                }
            }

            // Calculate percent movement of applications per month
            $applicationsSlicedMonthlyCounts = array_slice($applicationsMonthlyCounts, -2, 1);
            $applicationsPenultimateMonthCount = end($applicationsSlicedMonthlyCounts) ?: 0;
            $applicationsLastMonthCount = end($applicationsMonthlyCounts) ?: 0;
            $percentMovementApplicationsPerMonth = $applicationsPenultimateMonthCount != 0 ? round((($applicationsLastMonthCount - $applicationsPenultimateMonthCount) / $applicationsPenultimateMonthCount) * 100, 2) : 0;

            // Calculate percent movement of interviewed per month
            $interviewedSlicedMonthlyCounts = array_slice($interviewedMonthlyCounts, -2, 1);
            $interviewedPenultimateMonthCount = end($interviewedSlicedMonthlyCounts) ?: 0;
            $interviewedLastMonthCount = end($interviewedMonthlyCounts) ?: 0;
            $percentMovementInterviewedPerMonth = $interviewedPenultimateMonthCount != 0 ? round((($interviewedLastMonthCount - $interviewedPenultimateMonthCount) / $interviewedPenultimateMonthCount) * 100, 2) : 0;

            // Calculate percent movement of appointed per month
            $appointedSlicedMonthlyCounts = array_slice($appointedMonthlyCounts, -2, 1);
            $appointedPenultimateMonthCount = end($appointedSlicedMonthlyCounts) ?: 0;
            $appointedLastMonthCount = end($appointedMonthlyCounts) ?: 0;
            $percentMovementAppointedPerMonth = $appointedPenultimateMonthCount != 0 ? round((($appointedLastMonthCount - $appointedPenultimateMonthCount) / $appointedPenultimateMonthCount) * 100, 2) : 0;

            // Calculate percent movement of rejected per month
            $rejectedSlicedMonthlyCounts = array_slice($rejectedMonthlyCounts, -2, 1);
            $rejectedPenultimateMonthCount = end($rejectedSlicedMonthlyCounts) ?: 0;
            $rejectedLastMonthCount = end($rejectedMonthlyCounts) ?: 0;
            $percentMovementRejectedPerMonth = $rejectedPenultimateMonthCount != 0 ? round((($rejectedLastMonthCount - $rejectedPenultimateMonthCount) / $rejectedPenultimateMonthCount) * 100, 2) : 0;

            // Fetch chat data for the selected years
            $chatDataByYear = ChatTotalData::whereIn('year', [$startYear, $endYear])->get()->keyBy('year');

            $totalIncomingMessages = 0;
            $totalOutgoingMessages = 0;
            $incomingMessages = [];
            $outgoingMessages = [];

            for ($year = $startYear; $year <= $endYear; $year++) {
                if (!isset($chatDataByYear[$year])) {
                    continue;
                }

                $yearChatData = $chatDataByYear[$year];

                // Determine the months to process for the current year
                if ($year == $startYear && $year == $endYear) {
                    // If start year and end year are the same, process from start month to end month
                    $monthsToProcess = array_keys(array_filter($months, function ($value) use ($startMonth, $endMonth, $months) {
                        return $value >= $months[$startMonth] && $value <= $months[$endMonth];
                    }));
                } elseif ($year == $startYear) {
                    // For the start year, process from the start month to December
                    $monthsToProcess = array_keys(array_filter($months, function ($value) use ($startMonth, $months) {
                        return $value >= $months[$startMonth];
                    }));
                } elseif ($year == $endYear) {
                    // For the end year, process from January to the end month
                    $monthsToProcess = array_keys(array_filter($months, function ($value) use ($endMonth, $months) {
                        return $value <= $months[$endMonth];
                    }));
                } else {
                    // For the intermediate years, process all months
                    $monthsToProcess = array_keys($months);
                }

                // Add data for the current year's months
                foreach ($monthsToProcess as $month) {
                    $monthIncoming = strtolower($month) . '_incoming';
                    $monthOutgoing = strtolower($month) . '_outgoing';
                    $incomingCount = $yearChatData->$monthIncoming ?? 0;
                    $outgoingCount = $yearChatData->$monthOutgoing ?? 0;

                    $formattedMonth = $month . ' \'' . substr($year, -2);
                    $incomingMessages[] = $formattedMonth . ': ' . $incomingCount;
                    $outgoingMessages[] = $formattedMonth . ': ' . $outgoingCount;

                    // Sum the total incoming and outgoing messages
                    $totalIncomingMessages += $incomingCount;
                    $totalOutgoingMessages += $outgoingCount;
                }
            }

            // Fetch the applicants by position for the selected date range
            $applicantsByPosition = DB::table('applicant_monthly_data')
            ->join('applicant_total_data', 'applicant_monthly_data.applicant_total_data_id', '=', 'applicant_total_data.id')
            ->join('positions', 'applicant_monthly_data.category_id', '=', 'positions.id')
            ->where('applicant_monthly_data.category_type', 'Position')
            ->where(function ($query) use ($startYear, $endYear, $startMonth, $endMonth, $months) {
                if ($startYear == $endYear) {
                    $query->whereYear('applicant_total_data.year', $startYear)
                        ->where(function ($subQuery) use ($startMonth, $endMonth, $months) {
                            $subQuery->whereIn('applicant_monthly_data.month', array_keys(array_filter($months, function ($value) use ($startMonth, $endMonth, $months) {
                                return $value >= $months[$startMonth] && $value <= $months[$endMonth];
                            })));
                        });
                } else {
                    $query->where(function ($subQuery) use ($startYear, $startMonth, $months) {
                        $subQuery->whereYear('applicant_total_data.year', $startYear)
                                ->whereIn('applicant_monthly_data.month', array_keys(array_filter($months, function ($value) use ($startMonth, $months) {
                                    return $value >= $months[$startMonth];
                                })));
                    })->orWhere(function ($subQuery) use ($endYear, $endMonth, $months) {
                        $subQuery->whereYear('applicant_total_data.year', $endYear)
                                ->whereIn('applicant_monthly_data.month', array_keys(array_filter($months, function ($value) use ($endMonth, $months) {
                                    return $value <= $months[$endMonth];
                                })));
                    })->orWhereBetween('applicant_total_data.year', [$startYear + 1, $endYear - 1]);
                }
            })
            ->select('positions.name', DB::raw('SUM(applicant_monthly_data.count) as total_applicants'))
            ->groupBy('positions.name')
            ->get()
            ->map(function ($item) {
                // Format for the chart
                return ['x' => $item->name, 'y' => (int) $item->total_applicants];
            })
            ->toArray();

            $averageTimeToHire = $totalApplicants == 0 ? 0 : round($totalTimeToHire / $totalApplicants);
            $totalAbsorptionRate = $totalApplicants == 0 ? 0 : round($totalAppointed / $totalApplicants * 100);

            //Data to return
            $data = [
                'totalApplicants' => $totalApplicants,
                'totalAppointed' => $totalAppointed,
                'applicantsPerProvince' => $applicantsPerProvince,
                'applicantsByRace' => $applicantsByRace,
                'totalApplicantsPerMonth' => $totalApplicantsPerMonth,
                'applicationsPerMonth' => $totalApplicantsPerMonth,
                'interviewedPerMonth' => $interviewedPerMonth,
                'appointedPerMonth' => $appointedPerMonth,
                'rejectedPerMonth' => $rejectedPerMonth,
                'percentMovementApplicationsPerMonth' => $percentMovementApplicationsPerMonth,
                'percentMovementInterviewedPerMonth' => $percentMovementInterviewedPerMonth,
                'percentMovementAppointedPerMonth' => $percentMovementAppointedPerMonth,
                'percentMovementRejectedPerMonth' => $percentMovementRejectedPerMonth,
                'totalIncomingMessages' => $totalIncomingMessages,
                'totalOutgoingMessages' => $totalOutgoingMessages,
                'incomingMessages' => $incomingMessages,
                'outgoingMessages' => $outgoingMessages,
                'applicantsByPosition' => $applicantsByPosition,
                'totalTimeToHire' => $totalTimeToHire,
                'averageTimeToHire' => $averageTimeToHire,
                'totalAbsorptionRate' => $totalAbsorptionRate,
            ];

            // Return the aggregated data
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Data updated successfully!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate % Movement
    |--------------------------------------------------------------------------
    */

    private function calculatePercentMovement($data)
    {
        if (count($data) < 2) {
            return 0;
        }

        $sumOfData = array_sum($data);
        $lastMonth = end($data);
        $penultimateMonth = prev($data);

        if ($sumOfData > 0) {
            return round((($lastMonth - $penultimateMonth) / $sumOfData) * 100, 2);
        } else {
            return 0;
        }
    }
}
