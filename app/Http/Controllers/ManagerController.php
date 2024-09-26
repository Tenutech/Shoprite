<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Store;
use App\Models\Message;
use App\Models\Vacancy;
use App\Models\Position;
use App\Models\Interview;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Shortlist;
use App\Models\ReminderSetting;
use App\Models\ApplicantTotalData;
use App\Models\ApplicantMonthlyData;
use App\Models\ApplicantMonthlyStoreData;
use App\Services\DataService\ApplicantDataService;
use App\Services\DataService\VacancyDataService;
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
    public function __construct(ApplicantDataService $applicantDataService, VacancyDataService $vacancyDataService)
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

            //Store
            $store = Store::with([
                'brand',
                'town',
                'region',
                'division'
            ])
            ->where('id', $authUser->store_id)
            ->first();

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

            // Get the delay from ReminderSetting where type is 'shortlist_created_no_interview'
            $reminderSetting = ReminderSetting::where('type', 'shortlist_created_no_interview')->first();
            $delayDays = $reminderSetting ? $reminderSetting->delay : 1;

            // Get the current date and calculate the cutoff date based on the delay
            $cutoffDate = Carbon::now()->subDays($delayDays);

            // Query to find the first shortlist where either `applicant_ids` is null/empty OR no interviews exist
            $shortlist = Shortlist::where('user_id', $authUserId)
            ->where(function ($query) {
                // Check if applicant_ids is null or an empty JSON array
                $query->whereNull('applicant_ids')
                    ->orWhere('applicant_ids', '=', '')
                    ->orWhereRaw('JSON_LENGTH(applicant_ids) = 0')
                    // If applicant_ids is not empty, check that there are no interviews
                    ->orWhereHas('vacancy', function ($subquery) {
                        $subquery->doesntHave('interviews');
                    });
            })
            // Apply the created_at condition to all results
            ->where('created_at', '<=', $cutoffDate)
            ->first(); // Get the first matching shortlist

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

            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();

            $storeId = $authUser->store_id;

            $storeAverageTimeToShortlist = 0;
            $storeAverageTimeToHire = 0;
            $adoptionRate = 0;
            $averageScores = [];

            if ($storeId !== null) {
                $storeAverageTimeToShortlist = $this->vacancyDataService->getStoreAverageTimeToShortlist($storeId);
                $storeAverageTimeToHire = $this->vacancyDataService->getStoreAverageTimeToHire($storeId);
                $adoptionRate = $this->vacancyDataService->getStoreVacancyFillRate($storeId, null, $startDate, $endDate);
                $placedApplicants = $this->applicantDataService->getPlacedApplicantsWithScoresForStoreAndDateRange($storeId, $startDate, $endDate);
                $averageScores = $this->applicantDataService->calculateAverageScores($placedApplicants);
            }

            return view('manager/home', [
                'store' => $store,
                'vacancies' => $vacancies,
                'shortlist' => $shortlist,
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
                'storeAverageTimeToShortlist' => $storeAverageTimeToShortlist,
                'storeAverageTimeToHire' => $storeAverageTimeToHire,
                'adoptionRate' => $adoptionRate,
                'averageScores' => $averageScores,
            ]);
        }
        return view('404');
    }
}
