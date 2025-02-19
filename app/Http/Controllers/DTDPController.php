<?php

namespace App\Http\Controllers;

use App\Models\ReminderSetting;
use App\Models\Division;
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

class DTDPController extends Controller
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
     * Display the dtdp dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Check if the 'dtdp/home' view exists
        if (view()->exists('dtdp/home')) {
            // Retrieve the ID of the currently authenticated user
            $authUserId = Auth::id();

            // Fetch the authenticated user
            $authUser = User::find($authUserId);

            // Fetch the division
            $division = Division::where('id', $authUser->division_id)->first();

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

            // Define the warning period in days
            $warningDays = 4;

            // Fetch the vacancy posting duration setting from the database
            $vacancyNoInterviewPostingDurationSetting = Setting::where('key', 'vacancy_posting_duration')->first();
            $vacancyNoInterviewPostingDays = $vacancyNoInterviewPostingDurationSetting ? (int)$vacancyNoInterviewPostingDurationSetting->value : 14; // Default to 14 days if not set

            // Fetch all vacancies older than the specified duration
            $expiryDateNoInterview = Carbon::now()->subDays($vacancyNoInterviewPostingDays);
            $expiryDateNoInterviewCuttOff = Carbon::now()->subDays($vacancyNoInterviewPostingDays + $warningDays);

            $vacanciesNoInterview = Vacancy::where('user_id', $authUserId)
            ->whereBetween('created_at', [$expiryDateNoInterviewCuttOff, $expiryDateNoInterview])
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
                ->map(function ($vacancy) use ($expiryDateNoInterview) {
                    $vacancy->days_until_deletion = max(0, $expiryDateNoInterview->diffInDays($vacancy->created_at));
                    return $vacancy;
                });

            // Fetch the vacancy posting duration setting for no appointment
            $vacancyNoAppointmentPostingDurationSetting = Setting::where('key', 'vacancy_posting_duration_no_appointment')->first();
            $vacancyNoAppointmentPostingDays = $vacancyNoAppointmentPostingDurationSetting ? (int)$vacancyNoAppointmentPostingDurationSetting->value : 30;  // Default to 30 days if not set

            // Fetch all vacancies older than the specified duration
            $expiryDateNoAppointment = Carbon::now()->subDays($vacancyNoAppointmentPostingDays);
            $expiryDateNoAppointmentCuttOff = Carbon::now()->subDays($vacancyNoAppointmentPostingDays + $warningDays);

            $vacanciesNoAppointment = Vacancy::where('user_id', $authUserId)
                ->whereBetween('created_at', [$expiryDateNoAppointmentCuttOff, $expiryDateNoAppointment])
                ->where('deleted', 'No')
                ->where('auto_deleted', 'No')
                ->where('open_positions', '>', 0)
                ->get()
                ->map(function ($vacancy) use ($expiryDateNoAppointment) {
                    $vacancy->days_until_deletion = max(0, $expiryDateNoAppointment->diffInDays($vacancy->created_at));
                    return $vacancy;
                });

            // Return the 'dtdp/home' view with the calculated data
            return view('dtdp/home', [
                'division' => $division,
                'shortlist' => $shortlist,
                'vacanciesNoInterview' => $vacanciesNoInterview,
                'vacanciesNoAppointment' => $vacanciesNoAppointment,
            ]);
        }

        // If the view 'dtdp/home' does not exist, return a 404 error page
        return view('404');
    }

    /**
     * Update the dtdp dashboard data based on a selected date range.
     *
     * This method is triggered via an AJAX request and retrieves
     * updated statistics for the dtdp dashboard, including vacancy,
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

            // Set the type to 'division' to filter vacancies by the specific division ID in the query
            $type = 'division';

            // Get the division ID for the authenticated user
            $divisionId = $authUser->division_id;

            // Get the max proximity from store
            $maxDistanceFromStore = Setting::where('key', 'max_distance_from_store')->first()->value ?? 50;

            // Initialize variables to 0 or empty before the null check

            // Step 1: Initialize vacancy data
            $divisionTotalVacancies = 0;
            $divisionTotalVacanciesFilled = 0;

            // Step 2: Initialize interview data
            $divisionTotalInterviewsScheduled = 0;
            $divisionTotalInterviewsCompleted = 0;

            // Step 3: Initialize appointed and regretted applicant data
            $divisionTotalApplicantsAppointed = 0;
            $divisionTotalApplicantsRegretted = 0;

            // Step 4: Initialize time data
            $divisionAverageTimeToShortlist = 0;
            $divisionAverageTimeToHire = 0;
            $divisionAdoptionRate = 0;

            // Step 5: Initialize proximity data
            $divisionAverageDistanceTalentPoolApplicants = 0;
            $divisionAverageDistanceApplicantsAppointed = 0;

            // Step 6: Fetch applicant score data
            $divisionAverageScoreApplicantsAppointed = 0;
            $divisionAverageAssessmentScoreApplicantsAppointed = 0;

            // Step 7: Fetch talent pool data
            $divisionTalentPoolApplicants = 0;
            $divisionTalentPoolApplicantsByMonth = [];

            // Step 8: Fetch applicants appointed data
            $divisionApplicantsAppointed = 0;
            $divisionApplicantsAppointedByMonth = [];

            // Step 9: Fetch applicant demographic data
            $divisionTalentPoolApplicantsDemographic = [];
            $divisionInterviewedApplicantsDemographic = [];
            $divisionAppointedApplicantsDemographic = [];

            // Check if the authenticated user is associated with a division
            if ($divisionId !== null) {
                // Step 1: Fetch vacancy data from VacancyDataService
                $divisionTotalVacancies = $this->vacancyDataService->getTotalVacancies($type, $divisionId, $startDate, $endDate);
                $divisionTotalVacanciesFilled = $this->vacancyDataService->getTotalVacanciesFilled($type, $divisionId, $startDate, $endDate);

                // Step 2: Fetch interview data from VacancyDataService
                $divisionTotalInterviewsScheduled = $this->vacancyDataService->getTotalInterviewsScheduled($type, $divisionId, $startDate, $endDate);
                $divisionTotalInterviewsCompleted = $this->vacancyDataService->getTotalInterviewsCompleted($type, $divisionId, $startDate, $endDate);

                // Step 3: Fetch appointed and regretted applicant data from VacancyDataService
                $divisionTotalApplicantsAppointed = $this->vacancyDataService->getTotalApplicantsAppointed($type, $divisionId, $startDate, $endDate);
                $divisionTotalApplicantsRegretted = $this->vacancyDataService->getTotalApplicantsRegretted($type, $divisionId, $startDate, $endDate);

                // Step 4: Fetch time data from VacancyDataService
                $divisionAverageTimeToShortlist = $this->vacancyDataService->getAverageTimeToShortlist($type, $divisionId, $startDate, $endDate);
                $divisionAverageTimeToHire = $this->vacancyDataService->getAverageTimeToHire($type, $divisionId, $startDate, $endDate);
                $divisionAdoptionRate = ($divisionTotalVacancies > 0) ? round($divisionTotalVacanciesFilled / $divisionTotalVacancies * 100) : 0;

                // Step 5: Fetch proximity data from ApplicantProximityService
                $divisionAverageDistanceTalentPoolApplicants = $this->applicantProximityService->getAverageDistanceTalentPoolApplicants($type, $divisionId, $startDate, $endDate, $maxDistanceFromStore);
                $divisionAverageDistanceApplicantsAppointed = $this->applicantProximityService->getAverageDistanceApplicantsAppointed($type, $divisionId, $startDate, $endDate);

                // Step 6: Fetch applicant score data from ApplicantDataService
                $divisionAverageScoreApplicantsAppointed = $this->applicantDataService->getAverageScoreApplicantsAppointed($type, $divisionId, $startDate, $endDate);
                $divisionAverageAssessmentScoreApplicantsAppointed = $this->applicantDataService->getAverageAssessmentScoreApplicantsAppointed($type, $divisionId, $startDate, $endDate);

                // Step 7: Fetch talent pool data from applicantProximityService
                $divisionTalentPoolApplicants = $this->applicantProximityService->getTalentPoolApplicants($type, $divisionId, $startDate, $endDate, $maxDistanceFromStore);
                $divisionTalentPoolApplicantsByMonth = $this->applicantProximityService->getTalentPoolApplicantsByMonth($type, $divisionId, $startDate, $endDate, $maxDistanceFromStore);

                // Step 8: Fetch applicants appointed data from vacancyDataService
                $divisionApplicantsAppointed = $this->vacancyDataService->getApplicantsAppointed($type, $divisionId, $startDate, $endDate);
                $divisionApplicantsAppointedByMonth = $this->vacancyDataService->getApplicantsAppointedByMonth($type, $divisionId, $startDate, $endDate);

                // Step 9: Fetch applicant demographic data from applicantDataService
                $divisionTalentPoolApplicantsDemographic = $this->applicantDataService->getTalentPoolApplicantsDemographic($type, $divisionId, $startDate, $endDate, $maxDistanceFromStore);
                $divisionInterviewedApplicantsDemographic = $this->applicantDataService->getInterviewedApplicantsDemographic($type, $divisionId, $startDate, $endDate);
                $divisionAppointedApplicantsDemographic = $this->applicantDataService->getAppointedApplicantsDemographic($type, $divisionId, $startDate, $endDate);
            }

            //Data to return
            $data = [
                'divisionTotalVacancies' => $divisionTotalVacancies,
                'divisionTotalVacanciesFilled' => $divisionTotalVacanciesFilled,
                'divisionTotalInterviewsScheduled' => $divisionTotalInterviewsScheduled,
                'divisionTotalInterviewsCompleted' => $divisionTotalInterviewsCompleted,
                'divisionTotalApplicantsAppointed' => $divisionTotalApplicantsAppointed,
                'divisionTotalApplicantsRegretted' => $divisionTotalApplicantsRegretted,
                'divisionAverageTimeToShortlist' => $divisionAverageTimeToShortlist,
                'divisionAverageTimeToHire' => $divisionAverageTimeToHire,
                'divisionAdoptionRate' => $divisionAdoptionRate,
                'divisionAverageDistanceTalentPoolApplicants' => $divisionAverageDistanceTalentPoolApplicants,
                'divisionAverageDistanceApplicantsAppointed' => $divisionAverageDistanceApplicantsAppointed,
                'divisionAverageScoreApplicantsAppointed' => $divisionAverageScoreApplicantsAppointed,
                'divisionAverageAssessmentScoreApplicantsAppointed' => $divisionAverageAssessmentScoreApplicantsAppointed,
                'divisionTalentPoolApplicants' => $divisionTalentPoolApplicants,
                'divisionTalentPoolApplicantsByMonth' => $divisionTalentPoolApplicantsByMonth,
                'divisionApplicantsAppointed' => $divisionApplicantsAppointed,
                'divisionApplicantsAppointedByMonth' => $divisionApplicantsAppointedByMonth,
                'divisionTalentPoolApplicantsDemographic' => $divisionTalentPoolApplicantsDemographic,
                'divisionInterviewedApplicantsDemographic' => $divisionInterviewedApplicantsDemographic,
                'divisionAppointedApplicantsDemographic' => $divisionAppointedApplicantsDemographic
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
