<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Division;
use App\Models\Message;
use App\Models\Setting;
use App\Models\User;
use App\Services\DataService\ApplicantDataService;
use App\Services\DataService\ApplicantProximityService;
use App\Services\DataService\VacancyDataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\ActivityLogService;

class DPPController extends Controller
{
    private ActivityLogService $activityLogService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        ActivityLogService $activityLogService,
        ApplicantDataService $applicantDataService,
        ApplicantProximityService $applicantProximityService,
        VacancyDataService $vacancyDataService
    ) {
        $this->activityLogService = $activityLogService;
        $this->applicantDataService = $applicantDataService;
        $this->applicantProximityService = $applicantProximityService;
        $this->vacancyDataService = $vacancyDataService;
    }

    public function index()
    {
        if (view()->exists('dpp/home')) {
            // Retrieve the ID of the currently authenticated user
            $authUserId = Auth::id();

            // Fetch the authenticated user
            $authUser = User::find($authUserId);

            $divisionId = $authUser->division_id;

            // Define the date range (from the start of the year to the end of today)
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfDay();

            $data = $this->fetchData($divisionId, $startDate, $endDate);

            return view('dtdp/home', $data);
        }
        return view('404');
    }

    /**
     * Update the DTDP dashboard data based on a selected date range.
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

            $divisionId = $authUser->division_id;

            // Define the date range (from the request data)
            $startDate = Carbon::parse($request->input('startDate'))->startOfDay();
            $endDate = Carbon::parse($request->input('endDate'))->endOfDay();

            $data = $this->fetchData($divisionId, $startDate, $endDate);

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

    /**
     * Fetches various metrics for a division over a given date range.
     *
     * @param int $divisionId The ID of the division to fetch data for.
     * @param \Carbon\Carbon $startDate The start date of the range.
     * @param \Carbon\Carbon $endDate The end date of the range.
     * @return array An associative array containing division metrics.
     */
    private function fetchData(int $divisionId = null, Carbon $startDate, Carbon $endDate)
    {
        $division = [];
        $divisionTotalVacancies = 0;
        $divisionTotalVacanciesFilled = 0;
        $divisionTotalInterviewsScheduled = 0;
        $divisionTotalInterviewsCompleted = 0;
        $divisionTotalApplicantsAppointed = 0;
        $divisionTotalApplicantsRegretted = 0;
        $divisionAverageTimeToShortlist = 0;
        $divisionAverageTimeToHire = 0;
        $divisionAdoptionRate = 0;
        $divisionAverageDistanceApplicantsAppointed = 0;
        $divisionAverageScoreApplicantsAppointed = 0;
        $divisionTalentPoolApplicants = 0;
        $divisionApplicantsAppointed = 0;

        $maxDistanceFromStore = Setting::where('key', 'max_distance_from_store')->first()->value ?? 50;

        if ($divisionId !== null) {
            // Step 1: Fetch vacancy data from VacancyDataService
            $division = Division::where('id', $divisionId)
                ->first();
            $divisionTotalVacancies = $this->vacancyDataService->getDivisionTotalVacancies($divisionId, $startDate, $endDate);
            $divisionTotalVacanciesFilled = $this->vacancyDataService->getDivisionTotalVacanciesFilled($divisionId, $startDate, $endDate);

            // Step 2: Fetch interview data from VacancyDataService
            $divisionTotalInterviewsScheduled = $this->vacancyDataService->getDivisionTotalInterviewsScheduled($divisionId, $startDate, $endDate);
            $divisionTotalInterviewsCompleted = $this->vacancyDataService->getDivisionTotalInterviewsCompleted($divisionId, $startDate, $endDate);

            // Step 3: Fetch appointed and regretted applicant data from VacancyDataService
            $divisionTotalApplicantsAppointed = $this->vacancyDataService->getDivisionTotalApplicantsAppointed($divisionId, $startDate, $endDate);
            $divisionTotalApplicantsRegretted = $this->vacancyDataService->getDivisionTotalApplicantsRegretted($divisionId, $startDate, $endDate);

            // Step 4: Fetch time data from VacancyDataService
            $divisionAverageTimeToShortlist = $this->vacancyDataService->getDivisionAverageTimeToShortlist($divisionId, $startDate, $endDate);
            $divisionAverageTimeToHire = $this->vacancyDataService->getDivisionAverageTimeToHire($divisionId, $startDate, $endDate);
            $divisionAdoptionRate = ($divisionTotalVacancies > 0) ? round($divisionTotalVacanciesFilled / $divisionTotalVacancies * 100) : 0;

            // Step 5: Fetch proximity data from ApplicantProximityService
            $divisionAverageDistanceApplicantsAppointed = $this->applicantProximityService->calculateProximityForDivision($divisionId, $startDate, $endDate);

            // Step 6: Fetch applicant score data from ApplicantDataService
            $divisionAverageScoreApplicantsAppointed = $this->applicantDataService->getDivisionAverageScoreApplicantsAppointed($divisionId, $startDate, $endDate);

            // Step 7: Fetch talent pool data from applicantProximityService
            $divisionTalentPoolApplicants = $this->applicantProximityService->getDivisionTalentPoolApplicants($divisionId, $startDate, $endDate, $maxDistanceFromStore);
            $divisionTalentPoolApplicantsByMonth = $this->applicantProximityService->getDivisionTalentPoolApplicantsByMonth($divisionId, $startDate, $endDate, $maxDistanceFromStore);

            // Step 8:
            $divisionApplicantsAppointed = $this->applicantProximityService->getDivisionPlacedApplicants($divisionId, $startDate, $endDate);
            $divisionApplicantsAppointedByMonth = $this->applicantProximityService->getDivisionPlacedApplicantsByMonth($divisionId, $startDate, $endDate);
        }

        return [
            'division' => $division,
            'divisionTotalVacancies' => $divisionTotalVacancies,
            'divisionTotalVacanciesFilled' => $divisionTotalVacanciesFilled,
            'divisionTotalInterviewsScheduled' => $divisionTotalInterviewsScheduled,
            'divisionTotalInterviewsCompleted' => $divisionTotalInterviewsCompleted,
            'divisionTotalApplicantsAppointed' => $divisionTotalApplicantsAppointed,
            'divisionTotalApplicantsRegretted' => $divisionTotalApplicantsRegretted,
            'divisionAverageTimeToShortlist' => $divisionAverageTimeToShortlist,
            'divisionAverageTimeToHire' => $divisionAverageTimeToHire,
            'divisionAdoptionRate' => $divisionAdoptionRate,
            'divisionAverageDistanceApplicantsAppointed' => $divisionAverageDistanceApplicantsAppointed,
            'divisionAverageScoreApplicantsAppointed' => $divisionAverageScoreApplicantsAppointed,
            'divisionTalentPoolApplicants' => $divisionTalentPoolApplicants,
            'divisionTalentPoolApplicantsByMonth' => $divisionTalentPoolApplicantsByMonth,
            'divisionApplicantsAppointed' => $divisionApplicantsAppointed,
            'divisionApplicantsAppointedByMonth' => $divisionApplicantsAppointedByMonth,
        ];
    }
}
