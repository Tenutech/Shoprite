<?php

namespace App\Http\Controllers;

use App\Models\ReminderSetting;
use App\Models\Store;
use App\Models\Shortlist;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vacancy;
use App\Services\DataService\ApplicantDataService;
use App\Services\DataService\ApplicantProximityService;
use App\Services\DataService\VacancyDataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManagerController extends Controller
{
    protected $applicantDataService;
    protected $applicantProximityService;
    protected $vacancyDataService;

    /**
     * Constructor method to initialize services and apply middleware.
     *
     * @param ApplicantDataService $applicantDataService
     * @param ApplicantProximityService $applicantProximityService
     * @param VacancyDataService $vacancyDataService
     * @return void
     */
    public function __construct(
        ApplicantDataService $applicantDataService,
        ApplicantProximityService $applicantProximityService,
        VacancyDataService $vacancyDataService
    ) {
        // Apply 'auth' and 'verified' middleware to ensure user is authenticated and verified
        $this->middleware(['auth', 'verified']);

        // Inject required services
        $this->applicantDataService = $applicantDataService;
        $this->applicantProximityService = $applicantProximityService;
        $this->vacancyDataService = $vacancyDataService;
    }

    /**
     * Display the manager dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Check if the 'manager/home' view exists
        if (view()->exists('manager/home')) {
            // Retrieve the ID of the currently authenticated user
            $authUserId = Auth::id();

            // Fetch the authenticated user
            $authUser = User::find($authUserId);

            // Fetch the store with its related brand, town, region, and division
            $store = Store::with(['brand', 'town', 'region', 'division'])
                ->where('id', $authUser->store_id)
                ->first();

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
            ->whereHas('vacancy', function ($query) {
                $query->where('deleted', 'No'); // apply this to all shortlist's vacancies
            })
            // Apply the created_at condition to all results
            ->where('created_at', '<=', $cutoffDate)
            ->first(); // Get the first matching shortlist

            // Define the warning period in days
            $warningDays = 4;

            // Fetch the vacancy posting duration setting from the database
            $vacancyNoInterviewPostingDurationSetting = Setting::where('key', 'vacancy_posting_duration')->first();
            $vacancyNoInterviewPostingDays = $vacancyNoInterviewPostingDurationSetting ? (int)$vacancyNoInterviewPostingDurationSetting->value : 14; // Default to 14 days if not set

            // Fetch all vacancies older than the specified duration
            $expiryDateNoInterview = Carbon::now()->subDays($vacancyNoInterviewPostingDays);
            $expiryDateNoInterviewCuttOff = Carbon::now()->subDays($vacancyNoInterviewPostingDays + $warningDays);

            $vacanciesNoInterview = Vacancy::where('user_id', $authUserId)
                ->where('created_at', '<=', Carbon::now()->subDays($vacancyNoInterviewPostingDays - $warningDays)) // Ensure we are within the warning period
                ->where('deleted', 'No')
                ->where('auto_deleted', 'No')
                ->where(function ($query) {
                    $query->doesntHave('shortlists') // Vacancies with no shortlists
                        ->orWhereHas('shortlists', function ($subQuery) {
                            $subQuery->whereNull('applicant_ids')
                                    ->orWhereRaw("json_length(applicant_ids) = 0");
                        });
                })
                ->doesntHave('appointed') // Ensure no applicants have been appointed
                ->doesntHave('interviews') // Ensure no interviews exist
                ->get()
                ->map(function ($vacancy) use ($vacancyNoInterviewPostingDays) {
                    // Calculate the deletion date
                    $deletionDate = Carbon::parse($vacancy->created_at)->addDays($vacancyNoInterviewPostingDays);

                    // Calculate remaining days using hours to avoid rounding issues
                    $vacancy->days_until_deletion = max(0, ceil(Carbon::now()->diffInHours($deletionDate, false) / 24));

                    return $vacancy;
                });

            // Fetch the vacancy posting duration setting for no appointment
            $vacancyNoAppointmentPostingDurationSetting = Setting::where('key', 'vacancy_posting_duration_no_appointment')->first();
            $vacancyNoAppointmentPostingDays = $vacancyNoAppointmentPostingDurationSetting ? (int)$vacancyNoAppointmentPostingDurationSetting->value : 30;  // Default to 30 days if not set

            // Fetch all vacancies older than the specified duration
            $expiryDateNoAppointment = Carbon::now()->subDays($vacancyNoAppointmentPostingDays);
            $expiryDateNoAppointmentCuttOff = Carbon::now()->subDays($vacancyNoAppointmentPostingDays + $warningDays);

            $vacanciesNoAppointment = Vacancy::where('user_id', $authUserId)
                ->where('created_at', '<=', Carbon::now()->subDays($vacancyNoAppointmentPostingDays - $warningDays)) // Ensure we are within the warning period
                ->where('deleted', 'No')
                ->where('auto_deleted', 'No')
                ->where('open_positions', '>', 0)
                ->get()
                ->map(function ($vacancy) use ($vacancyNoAppointmentPostingDays) {
                    // Calculate the deletion date
                    $deletionDate = Carbon::parse($vacancy->created_at)->addDays($vacancyNoAppointmentPostingDays);

                    // Calculate remaining days using hours to avoid rounding issues
                    $vacancy->days_until_deletion = max(0, ceil(Carbon::now()->diffInHours($deletionDate, false) / 24));

                    return $vacancy;
                });

            // Return the 'manager/home' view with the calculated data
            return view('manager/home', [
                'store' => $store,
                'shortlist' => $shortlist,
                'vacanciesNoInterview' => $vacanciesNoInterview,
                'vacanciesNoAppointment' => $vacanciesNoAppointment,
            ]);
        }

        // If the view 'manager/home' does not exist, return a 404 error page
        return view('404');
    }

    /**
     * Update the manager dashboard data based on a selected date range.
     *
     * This method is triggered via an AJAX request and retrieves
     * updated statistics for the manager dashboard, including vacancy,
     * interview, applicant, and proximity data based on the selected
     * date range (startDate to endDate).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function updateDashboard(Request $request)
    {
        try {
            // Retrieve the ID of the currently authenticated user
            $authUserId = Auth::id();

            // Fetch the authenticated user
            $authUser = User::find($authUserId);

            // Define the date range (from the request data)
            $startDate = Carbon::parse($request->input('startDate'))->startOfDay();
            $endDate = Carbon::parse($request->input('endDate'))->endOfDay();

            // Set the type to 'store' to filter vacancies by the specific store ID in the query
            $type = 'store';

            // Get the store ID for the authenticated user
            $storeId = $authUser->store_id;

            // Get the max proximity from store
            $maxDistanceFromStore = Setting::where('key', 'max_distance_from_store')->first()->value ?? 50;

            // Initialize variables to 0 or empty before the null check

            // Step 1: Initialize vacancy data
            $storeTotalVacancies = 0;
            $storeTotalVacanciesFilled = 0;

            // Step 2: Initialize interview data
            $storeTotalInterviewsScheduled = 0;
            $storeTotalInterviewsCompleted = 0;

            // Step 3: Initialize appointed and regretted applicant data
            $storeTotalApplicantsAppointed = 0;
            $storeTotalApplicantsRegretted = 0;

            // Step 4: Initialize time data
            $storeAverageTimeToShortlist = 0;
            $storeAverageTimeToHire = 0;
            $storeAdoptionRate = 0;

            // Step 5: Initialize proximity data
            $storeAverageDistanceTalentPoolApplicants = 0;
            $storeAverageDistanceApplicantsAppointed = 0;

            // Step 6: Fetch applicant score data
            $storeAverageScoreApplicantsAppointed = 0;
            $storeAverageAssessmentScoreApplicantsAppointed = 0;

            // Step 7: Fetch talent pool data
            $storeTalentPoolApplicants = 0;
            $storeTalentPoolApplicantsByMonth = [];

            // Step 8: Fetch applicants appointed data
            $storeApplicantsAppointed = 0;
            $storeApplicantsAppointedByMonth = [];

            // Step 9: Fetch applicant demographic data
            $storeTalentPoolApplicantsDemographic = [];
            $storeInterviewedApplicantsDemographic = [];
            $storeAppointedApplicantsDemographic = [];

            // Check if the authenticated user is associated with a store
            if ($storeId !== null) {
                // Step 1: Fetch vacancy data from VacancyDataService
                $storeTotalVacancies = $this->vacancyDataService->getTotalVacancies($type, $storeId, $startDate, $endDate);
                $storeTotalVacanciesFilled = $this->vacancyDataService->getTotalVacanciesFilled($type, $storeId, $startDate, $endDate);

                // Step 2: Fetch interview data from VacancyDataService
                $storeTotalInterviewsScheduled = $this->vacancyDataService->getTotalInterviewsScheduled($type, $storeId, $startDate, $endDate);
                $storeTotalInterviewsCompleted = $this->vacancyDataService->getTotalInterviewsCompleted($type, $storeId, $startDate, $endDate);

                // Step 3: Fetch appointed and regretted applicant data from VacancyDataService
                $storeTotalApplicantsAppointed = $this->vacancyDataService->getTotalApplicantsAppointed($type, $storeId, $startDate, $endDate);
                $storeTotalApplicantsRegretted = $this->vacancyDataService->getTotalApplicantsRegretted($type, $storeId, $startDate, $endDate);

                // Step 4: Fetch time data from VacancyDataService
                $storeAverageTimeToShortlist = $this->vacancyDataService->getAverageTimeToShortlist($type, $storeId, $startDate, $endDate);
                $storeAverageTimeToHire = $this->vacancyDataService->getAverageTimeToHire($type, $storeId, $startDate, $endDate);
                $storeAdoptionRate = ($storeTotalVacancies > 0) ? round($storeTotalVacanciesFilled / $storeTotalVacancies * 100) : 0;

                // Step 5: Fetch proximity data from ApplicantProximityService
                $storeAverageDistanceTalentPoolApplicants = $this->applicantProximityService->getAverageDistanceTalentPoolApplicants($type, $storeId, $startDate, $endDate, $maxDistanceFromStore);
                $storeAverageDistanceApplicantsAppointed = $this->applicantProximityService->getAverageDistanceApplicantsAppointed($type, $storeId, $startDate, $endDate);

                // Step 6: Fetch applicant score data from ApplicantDataService
                $storeAverageScoreApplicantsAppointed = $this->applicantDataService->getAverageScoreApplicantsAppointed($type, $storeId, $startDate, $endDate);
                $storeAverageAssessmentScoreApplicantsAppointed = $this->applicantDataService->getAverageAssessmentScoreApplicantsAppointed($type, $storeId, $startDate, $endDate);

                // Step 7: Fetch talent pool data from applicantProximityService
                $storeTalentPoolApplicants = $this->applicantProximityService->getTalentPoolApplicants($type, $storeId, $startDate, $endDate, $maxDistanceFromStore);
                $storeTalentPoolApplicantsByMonth = $this->applicantProximityService->getTalentPoolApplicantsByMonth($type, $storeId, $startDate, $endDate, $maxDistanceFromStore);

                // Step 8: Fetch applicants appointed data from vacancyDataService
                $storeApplicantsAppointed = $this->vacancyDataService->getApplicantsAppointed($type, $storeId, $startDate, $endDate);
                $storeApplicantsAppointedByMonth = $this->vacancyDataService->getApplicantsAppointedByMonth($type, $storeId, $startDate, $endDate);

                // Step 9: Fetch applicant demographic data from applicantDataService
                $storeTalentPoolApplicantsDemographic = $this->applicantDataService->getTalentPoolApplicantsDemographic($type, $storeId, $startDate, $endDate, $maxDistanceFromStore);
                $storeInterviewedApplicantsDemographic = $this->applicantDataService->getInterviewedApplicantsDemographic($type, $storeId, $startDate, $endDate);
                $storeAppointedApplicantsDemographic = $this->applicantDataService->getAppointedApplicantsDemographic($type, $storeId, $startDate, $endDate);
            }

            //Data to return
            $data = [
                'storeTotalVacancies' => $storeTotalVacancies,
                'storeTotalVacanciesFilled' => $storeTotalVacanciesFilled,
                'storeTotalInterviewsScheduled' => $storeTotalInterviewsScheduled,
                'storeTotalInterviewsCompleted' => $storeTotalInterviewsCompleted,
                'storeTotalApplicantsAppointed' => $storeTotalApplicantsAppointed,
                'storeTotalApplicantsRegretted' => $storeTotalApplicantsRegretted,
                'storeAverageTimeToShortlist' => $storeAverageTimeToShortlist,
                'storeAverageTimeToHire' => $storeAverageTimeToHire,
                'storeAdoptionRate' => $storeAdoptionRate,
                'storeAverageDistanceTalentPoolApplicants' => $storeAverageDistanceTalentPoolApplicants,
                'storeAverageDistanceApplicantsAppointed' => $storeAverageDistanceApplicantsAppointed,
                'storeAverageScoreApplicantsAppointed' => $storeAverageScoreApplicantsAppointed,
                'storeAverageAssessmentScoreApplicantsAppointed' => $storeAverageAssessmentScoreApplicantsAppointed,
                'storeTalentPoolApplicants' => $storeTalentPoolApplicants,
                'storeTalentPoolApplicantsByMonth' => $storeTalentPoolApplicantsByMonth,
                'storeApplicantsAppointed' => $storeApplicantsAppointed,
                'storeApplicantsAppointedByMonth' => $storeApplicantsAppointedByMonth,
                'storeTalentPoolApplicantsDemographic' => $storeTalentPoolApplicantsDemographic,
                'storeInterviewedApplicantsDemographic' => $storeInterviewedApplicantsDemographic,
                'storeAppointedApplicantsDemographic' => $storeAppointedApplicantsDemographic
            ];

            // Return the updated data as JSON
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Data updated successfully!'
            ]);
        } catch (\Exception $e) {
            // Return other errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data!',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
