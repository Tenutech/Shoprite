<?php

namespace App\Http\Controllers;

use App\Models\Language;
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

use App\Services\ActivityLogService;
use App\Services\DataService\ApplicantDataService;
use App\Services\DataService\ChatDataService;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    protected $activityLogService;
    protected $applicantDataService;
    protected $chatDataService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        ActivityLogService $activityLogService, 
        ApplicantDataService $applicantDataService,
        ChatDataService $chatDataService)
    {
        $this->activityLogService = $activityLogService;
        $this->applicantDataService = $applicantDataService;
        $this->chatDataService = $chatDataService;

        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /**
     * Display a listing of the resource.
     *
     * This method handles the GET request to list resources. It might use request parameters
     * to filter or sort the returned resources based on specific criteria provided in the
     * request. This could include pagination, filtering by specific attributes, or sorting.
     *
     * @param  \Illuminate\Http\Request  $request  The request object containing any request parameters.
     * @return \Illuminate\Http\Response  Returns an HTTP response with the list of resources.
     */
    public function index(Request $request)
    {
        if (view()->exists('admin/home')) {

            // Define the models that are relevant for the activity log.
            $allowedModels = [
                'App\Models\Applicant',
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
            $activities = $this->activityLogService->getActivityLog($allowedModels, $authUserId, $authVacancyIds);
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

            // Positions
            $positions = Position::withCount('users')
                ->whereNotIn('id', [1, 10])
                ->orderBy('users_count', 'desc')
                ->take(10)
                ->get();

            /**
             * This whole section is geared to get the current year and previous year
             */
            // Current Year
            $currentYear = now()->year;
            $currentMonth = now()->month - 1;
            $previousYear = $currentYear - 1;
            $months = [];

            if (request()->isMethod('post')) {
                if ($request->fromDateTime && $request->toDateTime) {
                    
                    $fromDateTime = $request->fromDateTime;
                    $toDateTime = $request->toDateTime;
                   
                    $startOfYear = Carbon::createFromFormat('Y-m-d\TH:i', $fromDateTime);
                    $endOfYear = Carbon::createFromFormat('Y-m-d\TH:i', $toDateTime);
                    
                    $start = clone $startOfYear;
                    
                    while ($start->lessThanOrEqualTo($endOfYear)) {
                        // Add the current month to the array
                        $months[] = $start->format('M');
                        
                        // Move to the next month
                        $start->addMonthNoOverflow();
                    }
                    
                    $months = array_values($months);
                } else {
                  
                    $startOfYear = Carbon::now()->subYear()->startOfYear();
                    $endOfYear = Carbon::now()->subYear()->endOfYear();

                    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                }
               
            } else {
                $startOfYear = Carbon::now()->subYear()->startOfYear();
                $endOfYear = Carbon::now()->subYear()->endOfYear();

                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            }
       
            // This query could be removed since it is being used to check if data is present for a given year
            $currentYearData = $this->applicantDataService
                ->getApplicantTotalDataForDateRange($startOfYear, $endOfYear)->first();
            
            $previousYearData = [];

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
                $applicantsPerProvince = $this->applicantDataService->getApplicantsPerProvince($startOfYear, $endOfYear);
                  
                // Fetch applicants by race for the current year
                $applicantsByRaceCount = $this->applicantDataService->getApplicantsByRace(
                    $startOfYear, 
                    $endOfYear);

                // Combine both year's data
                $combinedApplicantsByRace = $applicantsByRaceCount;

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

                $totalApplicantsPerMonth = $this->applicantDataService->getApplicationsPerMonth(
                    $startOfYear, 
                    $endOfYear
                );
               
                // Fetch application data for the current year
                $applicationsCurrentYear = $this->applicantDataService->getApplicationsCountPerMonth(
                    $startOfYear, 
                    $endOfYear
                );
 
                // Combine both year's data
                $combinedApplications = $applicationsCurrentYear;

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
                $interviewsCountsPerMonth = $this->applicantDataService->getInterviewsCountPerMonth(
                    $startOfYear, 
                    $endOfYear
                );

                // Combine both year's data
                $combinedInterviews = $interviewsCountsPerMonth;

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
                $appointedCountPerMonth = $this->applicantDataService->getAppointedCountPerMonth(
                    $startOfYear, 
                    $endOfYear
                );

                // Combine both year's data
                $combinedAppointed = $appointedCountPerMonth;

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
                $rejectedApplicantsPerMonth = $this->applicantDataService->getRejectedApplicants(
                    $startOfYear, 
                    $endOfYear
                );
                
                // Combine both year's data
                $combinedRejected = $rejectedApplicantsPerMonth;

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

            // Message Data
            $incomingData = $this->chatDataService->getIncomingChat($startOfYear, $endOfYear);
            $outgoingData = $this->chatDataService->getOutgoingChat($startOfYear, $endOfYear);

            $totalIncomingMessages = 0;
            $totalOutgoingMessages = 0;
            $incomingMessages = [];
            $outgoingMessages = [];
            
            foreach ($incomingData as $value) {
                $monthNumber = Carbon::createFromFormat('F', $value->month)->month;
                $incomingMessages[] = $value->count;
                $totalIncomingMessages += $value->count;
            }
      
            foreach ($outgoingData as $value) {
                $monthNumber = Carbon::createFromFormat('F', $value->month)->month;
                $outgoingMessages[] = $value->count;
                $totalOutgoingMessages += $value->count;
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
            
            // echo '<pre>';
            // // print_r($this->chatDataService->getIncomingChat("2024-01-01", "2024-12-31"));
            // print_r($incomingMessages);
            // print_r($outgoingMessages);
            // print_r($totalApplicantsPerMonth);

            // print_r($months);
            // exit;

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
                'months' => $months,
                'percentMovementApplicationsPerMonth' => $percentMovementApplicationsPerMonth,
                'percentMovementInterviewedPerMonth' => $percentMovementInterviewedPerMonth,
                'percentMovementAppointedPerMonth' => $percentMovementAppointedPerMonth,
                'percentMovementRejectedPerMonth' => $percentMovementRejectedPerMonth,
                'startOfYear' => $startOfYear,
                'endOfYear' => $endOfYear,
            ]);
        }
        return view('404');
    }
}
