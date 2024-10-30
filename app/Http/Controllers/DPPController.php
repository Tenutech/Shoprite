<?php

namespace App\Http\Controllers;

use App\Models\ReminderSetting;
use App\Models\Division;
use App\Models\Shortlist;
use App\Models\Setting;
use App\Models\User;
use App\Services\DataService\ApplicantDataService;
use App\Services\DataService\ApplicantProximityService;
use App\Services\DataService\VacancyDataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DPPController extends Controller
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
     * Display the dpp dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Check if the 'dpp/home' view exists
        if (view()->exists('dpp/home')) {
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

            // Define the date range (from the start of the year to the end of today)
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfDay();

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

            // Step 7: Fetch talent pool data
            $divisionTalentPoolApplicants = 0;
            $divisionTalentPoolApplicantsByMonth = [];

            // Step 8: Fetch applicants appointed data
            $divisionApplicantsAppointed = 0;
            $divisionApplicantsAppointedByMonth = [];

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

                // Step 7: Fetch talent pool data from applicantProximityService
                $divisionTalentPoolApplicants = $this->applicantProximityService->getTalentPoolApplicants($type, $divisionId, $startDate, $endDate, $maxDistanceFromStore);
                $divisionTalentPoolApplicantsByMonth = $this->applicantProximityService->getTalentPoolApplicantsByMonth($type, $divisionId, $startDate, $endDate, $maxDistanceFromStore);

                // Step 8: Fetch applicants appointed data from vacancyDataService
                $divisionApplicantsAppointed = $this->vacancyDataService->getApplicantsAppointed($type, $divisionId, $startDate, $endDate);
                $divisionApplicantsAppointedByMonth = $this->vacancyDataService->getApplicantsAppointedByMonth($type, $divisionId, $startDate, $endDate);
            }

            // Return the 'dpp/home' view with the calculated data
            return view('dpp/home', [
                'division' => $division,
                'shortlist' => $shortlist,
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
                'divisionTalentPoolApplicants' => $divisionTalentPoolApplicants,
                'divisionTalentPoolApplicantsByMonth' => $divisionTalentPoolApplicantsByMonth,
                'divisionApplicantsAppointed' => $divisionApplicantsAppointed,
                'divisionApplicantsAppointedByMonth' => $divisionApplicantsAppointedByMonth
            ]);
        }

        // If the view 'dpp/home' does not exist, return a 404 error page
        return view('404');
    }

    /**
     * Update the dpp dashboard data based on a selected date range.
     *
     * This method is triggered via an AJAX request and retrieves
     * updated statistics for the dpp dashboard, including vacancy,
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

            // Step 7: Fetch talent pool data
            $divisionTalentPoolApplicants = 0;
            $divisionTalentPoolApplicantsByMonth = [];

            // Step 8: Fetch applicants appointed data
            $divisionApplicantsAppointed = 0;
            $divisionApplicantsAppointedByMonth = [];

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

                // Step 7: Fetch talent pool data from applicantProximityService
                $divisionTalentPoolApplicants = $this->applicantProximityService->getTalentPoolApplicants($type, $divisionId, $startDate, $endDate, $maxDistanceFromStore);
                $divisionTalentPoolApplicantsByMonth = $this->applicantProximityService->getTalentPoolApplicantsByMonth($type, $divisionId, $startDate, $endDate, $maxDistanceFromStore);

                // Step 8: Fetch applicants appointed data from vacancyDataService
                $divisionApplicantsAppointed = $this->vacancyDataService->getApplicantsAppointed($type, $divisionId, $startDate, $endDate);
                $divisionApplicantsAppointedByMonth = $this->vacancyDataService->getApplicantsAppointedByMonth($type, $divisionId, $startDate, $endDate);
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
                'divisionTalentPoolApplicants' => $divisionTalentPoolApplicants,
                'divisionTalentPoolApplicantsByMonth' => $divisionTalentPoolApplicantsByMonth,
                'divisionApplicantsAppointed' => $divisionApplicantsAppointed,
                'divisionApplicantsAppointedByMonth' => $divisionApplicantsAppointedByMonth
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
