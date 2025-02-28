<?php

namespace App\Http\Controllers;

use App\Models\ReminderSetting;
use App\Models\Region;
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

class RPPController extends Controller
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
     * Display the rpp dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Check if the 'rpp/home' view exists
        if (view()->exists('rpp/home')) {
            // Retrieve the ID of the currently authenticated user
            $authUserId = Auth::id();

            // Fetch the authenticated user
            $authUser = User::find($authUserId);

            // Fetch the region
            $region = Region::where('id', $authUser->region_id)->first();

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

            // Return the 'rpp/home' view with the calculated data
            return view('rpp/home', [
                'region' => $region,
                'shortlist' => $shortlist,
                'vacanciesNoInterview' => $vacanciesNoInterview,
                'vacanciesNoAppointment' => $vacanciesNoAppointment,
            ]);
        }

        // If the view 'rpp/home' does not exist, return a 404 error page
        return view('404');
    }

    /**
     * Update the rpp dashboard data based on a selected date range.
     *
     * This method is triggered via an AJAX request and retrieves
     * updated statistics for the rpp dashboard, including vacancy,
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

            // Set the type to 'region' to filter vacancies by the specific region ID in the query
            $type = 'region';

            // Get the region ID for the authenticated user
            $regionId = $authUser->region_id;

            // Get the max proximity from store
            $maxDistanceFromStore = Setting::where('key', 'max_distance_from_store')->first()->value ?? 50;

            // Initialize variables to 0 or empty before the null check

            // Step 1: Initialize vacancy data
            $regionTotalVacancies = 0;
            $regionTotalVacanciesFilled = 0;

            // Step 2: Initialize interview data
            $regionTotalInterviewsScheduled = 0;
            $regionTotalInterviewsCompleted = 0;

            // Step 3: Initialize appointed and regretted applicant data
            $regionTotalApplicantsAppointed = 0;
            $regionTotalApplicantsRegretted = 0;

            // Step 4: Initialize time data
            $regionAverageTimeToShortlist = 0;
            $regionAverageTimeToHire = 0;
            $regionAdoptionRate = 0;

            // Step 5: Initialize proximity data
            $regionAverageDistanceTalentPoolApplicants = 0;
            $regionAverageDistanceApplicantsAppointed = 0;

            // Step 6: Fetch applicant score data
            $regionAverageScoreApplicantsAppointed = 0;
            $regionAverageAssessmentScoreApplicantsAppointed = 0;

            // Step 7: Fetch talent pool data
            $regionTalentPoolApplicants = 0;
            $regionTalentPoolApplicantsByMonth = [];

            // Step 8: Fetch applicants appointed data
            $regionApplicantsAppointed = 0;
            $regionApplicantsAppointedByMonth = [];

            // Step 9: Fetch applicant demographic data
            $regionTalentPoolApplicantsDemographic = [];
            $regionInterviewedApplicantsDemographic = [];
            $regionAppointedApplicantsDemographic = [];

            // Check if the authenticated user is associated with a region
            if ($regionId !== null) {
                // Step 1: Fetch vacancy data from VacancyDataService
                $regionTotalVacancies = $this->vacancyDataService->getTotalVacancies($type, $regionId, $startDate, $endDate);
                $regionTotalVacanciesFilled = $this->vacancyDataService->getTotalVacanciesFilled($type, $regionId, $startDate, $endDate);

                // Step 2: Fetch interview data from VacancyDataService
                $regionTotalInterviewsScheduled = $this->vacancyDataService->getTotalInterviewsScheduled($type, $regionId, $startDate, $endDate);
                $regionTotalInterviewsCompleted = $this->vacancyDataService->getTotalInterviewsCompleted($type, $regionId, $startDate, $endDate);

                // Step 3: Fetch appointed and regretted applicant data from VacancyDataService
                $regionTotalApplicantsAppointed = $this->vacancyDataService->getTotalApplicantsAppointed($type, $regionId, $startDate, $endDate);
                $regionTotalApplicantsRegretted = $this->vacancyDataService->getTotalApplicantsRegretted($type, $regionId, $startDate, $endDate);

                // Step 4: Fetch time data from VacancyDataService
                $regionAverageTimeToShortlist = $this->vacancyDataService->getAverageTimeToShortlist($type, $regionId, $startDate, $endDate);
                $regionAverageTimeToHire = $this->vacancyDataService->getAverageTimeToHire($type, $regionId, $startDate, $endDate);
                $regionAdoptionRate = ($regionTotalVacancies > 0) ? round($regionTotalVacanciesFilled / $regionTotalVacancies * 100) : 0;

                // Step 5: Fetch proximity data from ApplicantProximityService
                $regionAverageDistanceTalentPoolApplicants = $this->applicantProximityService->getAverageDistanceTalentPoolApplicants($type, $regionId, $startDate, $endDate, $maxDistanceFromStore);
                $regionAverageDistanceApplicantsAppointed = $this->applicantProximityService->getAverageDistanceApplicantsAppointed($type, $regionId, $startDate, $endDate);

                // Step 6: Fetch applicant score data from ApplicantDataService
                $regionAverageScoreApplicantsAppointed = $this->applicantDataService->getAverageScoreApplicantsAppointed($type, $regionId, $startDate, $endDate);
                $regionAverageAssessmentScoreApplicantsAppointed = $this->applicantDataService->getAverageAssessmentScoreApplicantsAppointed($type, $regionId, $startDate, $endDate);

                // Step 7: Fetch talent pool data from applicantProximityService
                $regionTalentPoolApplicants = $this->applicantProximityService->getTalentPoolApplicants($type, $regionId, $startDate, $endDate, $maxDistanceFromStore);
                $regionTalentPoolApplicantsByMonth = $this->applicantProximityService->getTalentPoolApplicantsByMonth($type, $regionId, $startDate, $endDate, $maxDistanceFromStore);

                // Step 8: Fetch applicants appointed data from vacancyDataService
                $regionApplicantsAppointed = $this->vacancyDataService->getApplicantsAppointed($type, $regionId, $startDate, $endDate);
                $regionApplicantsAppointedByMonth = $this->vacancyDataService->getApplicantsAppointedByMonth($type, $regionId, $startDate, $endDate);

                // Step 9: Fetch applicant demographic data from applicantDataService
                $regionTalentPoolApplicantsDemographic = $this->applicantDataService->getTalentPoolApplicantsDemographic($type, $regionId, $startDate, $endDate, $maxDistanceFromStore);
                $regionInterviewedApplicantsDemographic = $this->applicantDataService->getInterviewedApplicantsDemographic($type, $regionId, $startDate, $endDate);
                $regionAppointedApplicantsDemographic = $this->applicantDataService->getAppointedApplicantsDemographic($type, $regionId, $startDate, $endDate);
            }

            //Data to return
            $data = [
                'regionTotalVacancies' => $regionTotalVacancies,
                'regionTotalVacanciesFilled' => $regionTotalVacanciesFilled,
                'regionTotalInterviewsScheduled' => $regionTotalInterviewsScheduled,
                'regionTotalInterviewsCompleted' => $regionTotalInterviewsCompleted,
                'regionTotalApplicantsAppointed' => $regionTotalApplicantsAppointed,
                'regionTotalApplicantsRegretted' => $regionTotalApplicantsRegretted,
                'regionAverageTimeToShortlist' => $regionAverageTimeToShortlist,
                'regionAverageTimeToHire' => $regionAverageTimeToHire,
                'regionAdoptionRate' => $regionAdoptionRate,
                'regionAverageDistanceTalentPoolApplicants' => $regionAverageDistanceTalentPoolApplicants,
                'regionAverageDistanceApplicantsAppointed' => $regionAverageDistanceApplicantsAppointed,
                'regionAverageScoreApplicantsAppointed' => $regionAverageScoreApplicantsAppointed,
                'regionAverageAssessmentScoreApplicantsAppointed' => $regionAverageAssessmentScoreApplicantsAppointed,
                'regionTalentPoolApplicants' => $regionTalentPoolApplicants,
                'regionTalentPoolApplicantsByMonth' => $regionTalentPoolApplicantsByMonth,
                'regionApplicantsAppointed' => $regionApplicantsAppointed,
                'regionApplicantsAppointedByMonth' => $regionApplicantsAppointedByMonth,
                'regionTalentPoolApplicantsDemographic' => $regionTalentPoolApplicantsDemographic,
                'regionInterviewedApplicantsDemographic' => $regionInterviewedApplicantsDemographic,
                'regionAppointedApplicantsDemographic' => $regionAppointedApplicantsDemographic
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
