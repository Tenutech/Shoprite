<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Vacancy;
use App\Models\Position;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\ApplicantTotalData;
use App\Models\ApplicantMonthlyData;
use App\Models\ApplicantMonthlyStoreData;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Spatie\Activitylog\Models\Activity;

class ManagerController extends Controller
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
    | Manager Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        if (view()->exists('manager/home')) {
            // Retrieve the ID of the currently authenticated user.
            $authUserId = Auth::id();

            //Auth User
            $authUser = User::find($authUserId);

            //Vacancies
            $vacancies = Vacancy::with([
                'user',
                'position.tags',
                'store.brand',
                'store.town',
                'type',
                'status',
                'applicants.applicant',
                'applications',
                'savedBy' => function ($query) use ($authUserId) {
                    $query->where('user_id', $authUserId);
                }
            ])
            ->withCount(['applications as total_applications'])
            ->withCount(['applications as applications_approved' => function ($query) {
                $query->where('approved', 'Yes');
            }])
            ->withCount(['applications as applications_rejected' => function ($query) {
                $query->where('approved', 'No');
            }])
            ->where('user_id', $authUserId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($vacancy) {
                $vacancy->encrypted_id = Crypt::encryptString($vacancy->id);
                $vacancy->encrypted_user_id = Crypt::encryptString($vacancy->user_id);
                return $vacancy;
            });

            // All Vacancies
            $allVacancies = Vacancy::with([
                'user',
                'position.tags',
                'store.brand',
                'store.town',
                'type',
                'status',
                'applicants.applicant',
                'applications',
                'savedBy' => function ($query) use ($authUserId) {
                    $query->where('user_id', $authUserId);
                }
            ])
            ->withCount(['applications as total_applications'])
            ->withCount(['applications as applications_approved' => function ($query) {
                $query->where('approved', 'Yes');
            }])
            ->withCount(['applications as applications_rejected' => function ($query) {
                $query->where('approved', 'No');
            }])
            ->where('status_id', 2)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($vacancy) {
                $vacancy->encrypted_id = Crypt::encryptString($vacancy->id);
                $vacancy->encrypted_user_id = Crypt::encryptString($vacancy->user_id);
                return $vacancy;
            });

            // Define the models that are relevant for the activity log.
            $allowedModels = [
                'App\Models\Applicant',
                'App\Models\Application',
                'App\Models\Vacancy',
                'App\Models\Message',
                'App\Models\User'
            ];

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

            $applicationsPerMonth = [];
            $interviewedPerMonth = [];
            $appointedPerMonth = [];
            $rejectedPerMonth = [];
            $percentMovementApplicationsPerMonth = 0;
            $percentMovementInterviewedPerMonth = 0;
            $percentMovementAppointedPerMonth = 0;
            $percentMovementRejectedPerMonth = 0;

            if ($currentYearData) {
                if ($authUser->store_id) {
                    // Fetch application data for the current year
                    $applicationsCurrentYear = ApplicantMonthlyData::join('applicant_monthly_store_data', 'applicant_monthly_data.id', '=', 'applicant_monthly_store_data.applicant_monthly_data_id')
                    ->where('applicant_monthly_data.category_type', 'Application')
                    ->where('applicant_monthly_data.applicant_total_data_id', $currentYearId)
                    ->where('applicant_monthly_store_data.store_id', $authUser->store_id)
                    ->whereIn('applicant_monthly_data.month', $queryMonthsCurrentYear)
                    ->get(['applicant_monthly_data.month', 'applicant_monthly_store_data.count']);

                    // Fetch application data for the previous year
                    $applicationsPreviousYear = ApplicantMonthlyData::join('applicant_monthly_store_data', 'applicant_monthly_data.id', '=', 'applicant_monthly_store_data.applicant_monthly_data_id')
                    ->where('applicant_monthly_data.category_type', 'Application')
                    ->where('applicant_monthly_data.applicant_total_data_id', $previousYearId)
                    ->where('applicant_monthly_store_data.store_id', $authUser->store_id)
                    ->whereIn('applicant_monthly_data.month', $queryMonthsPreviousYear)
                    ->get(['applicant_monthly_data.month', 'applicant_monthly_store_data.count']);

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

                    // Fetch interview data for the current year
                    $interviewsCurrentYear = ApplicantMonthlyData::join('applicant_monthly_store_data', 'applicant_monthly_data.id', '=', 'applicant_monthly_store_data.applicant_monthly_data_id')
                    ->where('applicant_monthly_data.category_type', 'Interviewed')
                    ->where('applicant_monthly_data.applicant_total_data_id', $currentYearId)
                    ->where('applicant_monthly_store_data.store_id', $authUser->store_id)
                    ->whereIn('applicant_monthly_data.month', $queryMonthsCurrentYear)
                    ->get(['applicant_monthly_data.month', 'applicant_monthly_store_data.count']);

                    // Fetch interview data for the previous year
                    $interviewsPreviousYear = ApplicantMonthlyData::join('applicant_monthly_store_data', 'applicant_monthly_data.id', '=', 'applicant_monthly_store_data.applicant_monthly_data_id')
                    ->where('applicant_monthly_data.category_type', 'Interviewed')
                    ->where('applicant_monthly_data.applicant_total_data_id', $previousYearId)
                    ->where('applicant_monthly_store_data.store_id', $authUser->store_id)
                    ->whereIn('applicant_monthly_data.month', $queryMonthsPreviousYear)
                    ->get(['applicant_monthly_data.month', 'applicant_monthly_store_data.count']);

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

                    // Fetch appointed data for the current year
                    $appointedCurrentYear = ApplicantMonthlyData::join('applicant_monthly_store_data', 'applicant_monthly_data.id', '=', 'applicant_monthly_store_data.applicant_monthly_data_id')
                    ->where('applicant_monthly_data.category_type', 'Appointed')
                    ->where('applicant_monthly_data.applicant_total_data_id', $currentYearId)
                    ->where('applicant_monthly_store_data.store_id', $authUser->store_id)
                    ->whereIn('applicant_monthly_data.month', $queryMonthsCurrentYear)
                    ->get(['applicant_monthly_data.month', 'applicant_monthly_store_data.count']);

                    // Fetch appointed data for the previous year
                    $appointedPreviousYear = ApplicantMonthlyData::join('applicant_monthly_store_data', 'applicant_monthly_data.id', '=', 'applicant_monthly_store_data.applicant_monthly_data_id')
                    ->where('applicant_monthly_data.category_type', 'Appointed')
                    ->where('applicant_monthly_data.applicant_total_data_id', $previousYearId)
                    ->where('applicant_monthly_store_data.store_id', $authUser->store_id)
                    ->whereIn('applicant_monthly_data.month', $queryMonthsPreviousYear)
                    ->get(['applicant_monthly_data.month', 'applicant_monthly_store_data.count']);

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

                    // Fetch rejected data for the current year
                    $rejectedCurrentYear = ApplicantMonthlyData::join('applicant_monthly_store_data', 'applicant_monthly_data.id', '=', 'applicant_monthly_store_data.applicant_monthly_data_id')
                    ->where('applicant_monthly_data.category_type', 'Rejected')
                    ->where('applicant_monthly_data.applicant_total_data_id', $currentYearId)
                    ->where('applicant_monthly_store_data.store_id', $authUser->store_id)
                    ->whereIn('applicant_monthly_data.month', $queryMonthsCurrentYear)
                    ->get(['applicant_monthly_data.month', 'applicant_monthly_store_data.count']);

                    // Fetch rejected data for the previous year
                    $rejectedPreviousYear = ApplicantMonthlyData::join('applicant_monthly_store_data', 'applicant_monthly_data.id', '=', 'applicant_monthly_store_data.applicant_monthly_data_id')
                    ->where('applicant_monthly_data.category_type', 'Rejected')
                    ->where('applicant_monthly_data.applicant_total_data_id', $previousYearId)
                    ->where('applicant_monthly_store_data.store_id', $authUser->store_id)
                    ->whereIn('applicant_monthly_data.month', $queryMonthsPreviousYear)
                    ->get(['applicant_monthly_data.month', 'applicant_monthly_store_data.count']);

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
                }

                //Percent movement from last month for applications
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

                //Percent movement from last month for interviews
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

                //Percent movement from last month for appointed
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

            return view('manager/home', [
                'vacancies' => $vacancies,
                'allVacancies' => $allVacancies,
                'activities' => $activities,
                'positions' => $positions,
                'currentYearData' => $currentYearData,
                'previousYearData' => $previousYearData,
                'applicationsPerMonth' => $applicationsPerMonth,
                'interviewedPerMonth' => $interviewedPerMonth,
                'appointedPerMonth' => $appointedPerMonth,
                'rejectedPerMonth' => $rejectedPerMonth,
                'totalApplications' => $sumOfApplications,
                'totalInterviews' => $sumOfInterviewed,
                'totalAppointed' => $sumOfAppointed,
                'totalRejected' => $sumOfRejected,
                'percentMovementApplicationsPerMonth' => $percentMovementApplicationsPerMonth,
                'percentMovementInterviewedPerMonth' => $percentMovementInterviewedPerMonth,
                'percentMovementAppointedPerMonth' => $percentMovementAppointedPerMonth,
                'percentMovementRejectedPerMonth' => $percentMovementRejectedPerMonth,
            ]);
        }
        return view('404');
    }
}
