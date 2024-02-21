<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Spatie\Activitylog\Models\Activity;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
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
            $activities = Activity::whereIn('subject_type', $allowedModels)
                ->where(function($query) use ($authUserId, $authVacancyIds) {
                    // Filter for activities where the 'causer' (the user who performed the action) is the authenticated user,
                    // and the action is one of 'created', 'updated', or 'deleted'.
                    $query->where('causer_id', $authUserId)
                        ->whereIn('event', ['created', 'updated', 'deleted']);
                })
                ->orWhere(function($q) use ($authUserId) {
                    // Include activities where the event is 'accessed' (e.g., a user viewed a vacancy or applicant profile),
                    // specifically for the authenticated user.
                    $q->where('event', 'accessed')
                    ->whereIn('description', ['job-overview.index', 'applicant-profile.index'])
                    ->where('causer_id', $authUserId);
                })
                ->orWhere(function($q) use ($authUserId) {
                    // Include activities related to messages where the authenticated user is the recipient ('to_id').
                    $q->where('subject_type', 'App\Models\Message')
                    ->where('properties->attributes->to_id', $authUserId)
                    ->where('event', 'created');
                })
                ->orWhere(function($q) use ($authVacancyIds) {
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
            ->map(function($url) {
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
            ->map(function($encryptedId) {
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
            }

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

            return view('admin/home',[
                'activities' => $activities,
                'positions' => $positions,
                'applicantsPerProvince' => $applicantsPerProvince,
                'applicantsByRace' => $applicantsByRace,
                'totalApplicantsPerMonth' => $totalApplicantsPerMonth,
                'totalIncomingMessages' => $totalIncomingMessages,
                'totalOutgoingMessages' => $totalOutgoingMessages,
                'incomingMessages' => $incomingMessages,
                'outgoingMessages' => $outgoingMessages,
                'applicantsByPosition' => $applicantsByPosition,
            ]);
        }
        return view('404');
    }


}