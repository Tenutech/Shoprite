<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\REgion;
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

class RPPController extends Controller
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
        if (view()->exists('rpp/home')) {
            // Retrieve the ID of the currently authenticated user
            $authUserId = Auth::id();

            // Fetch the authenticated user
            $authUser = User::find($authUserId);

            $regionId = $authUser->region_id;

            // Define the date range (from the start of the year to the end of today)
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfDay();

            $data = $this->fetchData($regionId, $startDate, $endDate);

            return view('rpp/home', $data);
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

            $regionId = $authUser->region_id;

            // Define the date range (from the request data)
            $startDate = Carbon::parse($request->input('startDate'))->startOfDay();
            $endDate = Carbon::parse($request->input('endDate'))->endOfDay();

            $data = $this->fetchData($regionId, $startDate, $endDate);

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
     * Fetches various metrics for a region over a given date range.
     *
     * @param int $regionId The ID of the region to fetch data for.
     * @param \Carbon\Carbon $startDate The start date of the range.
     * @param \Carbon\Carbon $endDate The end date of the range.
     * @return array An associative array containing region metrics.
     */
    private function fetchData(int $regionId = null, Carbon $startDate, Carbon $endDate)
    {
        $region = [];
        $regionTotalVacancies = 0;
        $regionTotalVacanciesFilled = 0;
        $regionTotalInterviewsScheduled = 0;
        $regionTotalInterviewsCompleted = 0;
        $regionTotalApplicantsAppointed = 0;
        $regionTotalApplicantsRegretted = 0;
        $regionAverageTimeToShortlist = 0;
        $regionAverageTimeToHire = 0;
        $regionAdoptionRate = 0;
        $regionAverageDistanceApplicantsAppointed = 0;
        $regionAverageScoreApplicantsAppointed = 0;
        $regionTalentPoolApplicants = 0;
        $regionApplicantsAppointed = 0;

        $maxDistanceFromStore = Setting::where('key', 'max_distance_from_store')->first()->value ?? 50;

        if ($regionId !== null) {
            // Step 1: Fetch vacancy data from VacancyDataService
            $region = Region::where('id', $regionId)
                ->first();
            $regionTotalVacancies = $this->vacancyDataService->getRegionTotalVacancies($regionId, $startDate, $endDate);
            $regionTotalVacanciesFilled = $this->vacancyDataService->getRegionTotalVacanciesFilled($regionId, $startDate, $endDate);

            // Step 2: Fetch interview data from VacancyDataService
            $regionTotalInterviewsScheduled = $this->vacancyDataService->getRegionTotalInterviewsScheduled($regionId, $startDate, $endDate);
            $regionTotalInterviewsCompleted = $this->vacancyDataService->getRegionTotalInterviewsCompleted($regionId, $startDate, $endDate);

            // Step 3: Fetch appointed and regretted applicant data from VacancyDataService
            $regionTotalApplicantsAppointed = $this->vacancyDataService->getRegionTotalApplicantsAppointed($regionId, $startDate, $endDate);
            $regionTotalApplicantsRegretted = $this->vacancyDataService->getRegionTotalApplicantsRegretted($regionId, $startDate, $endDate);

            // Step 4: Fetch time data from VacancyDataService
            $regionAverageTimeToShortlist = $this->vacancyDataService->getRegionAverageTimeToShortlist($regionId, $startDate, $endDate);
            $regionAverageTimeToHire = $this->vacancyDataService->getRegionAverageTimeToHire($regionId, $startDate, $endDate);
            $regionAdoptionRate = ($regionTotalVacancies > 0) ? round($regionTotalVacanciesFilled / $regionTotalVacancies * 100) : 0;

            // Step 5: Fetch proximity data from ApplicantProximityService
            $regionAverageDistanceApplicantsAppointed = $this->applicantProximityService->calculateProximityForRegion($regionId, $startDate, $endDate);

            // Step 6: Fetch applicant score data from ApplicantDataService
            $redionAverageScoreApplicantsAppointed = $this->applicantDataService->getRegionAverageScoreApplicantsAppointed($regionId, $startDate, $endDate);

            // Step 7: Fetch talent pool data from applicantProximityService
            $regionTalentPoolApplicants = $this->applicantProximityService->getRegionTalentPoolApplicants($regionId, $startDate, $endDate, $maxDistanceFromStore);
            $regionTalentPoolApplicantsByMonth = $this->applicantProximityService->getRegionTalentPoolApplicantsByMonth($regionId, $startDate, $endDate, $maxDistanceFromStore);

            // Step 8:
            $regionApplicantsAppointed = $this->applicantProximityService->getRegionPlacedApplicants($regionId, $startDate, $endDate);
            $regionApplicantsAppointedByMonth = $this->applicantProximityService->getRegionPlacedApplicantsByMonth($regionId, $startDate, $endDate);
        }

        return [
            'region' => $region,
            'regionTotalVacancies' => $regionTotalVacancies,
            'regionTotalVacanciesFilled' => $regionTotalVacanciesFilled,
            'regionTotalInterviewsScheduled' => $regionTotalInterviewsScheduled,
            'regionTotalInterviewsCompleted' => $regionTotalInterviewsCompleted,
            'regionTotalApplicantsAppointed' => $regionTotalApplicantsAppointed,
            'regionTotalApplicantsRegretted' => $regionTotalApplicantsRegretted,
            'regionAverageTimeToShortlist' => $regionAverageTimeToShortlist,
            'regionAverageTimeToHire' => $regionAverageTimeToHire,
            'regionAdoptionRate' => $regionAdoptionRate,
            'regionAverageDistanceApplicantsAppointed' => $regionAverageDistanceApplicantsAppointed,
            'regionAverageScoreApplicantsAppointed' => $regionAverageScoreApplicantsAppointed,
            'regionTalentPoolApplicants' => $regionTalentPoolApplicants,
            'regionTalentPoolApplicantsByMonth' => $regionTalentPoolApplicantsByMonth,
            'regionApplicantsAppointed' => $regionApplicantsAppointed,
            'regionApplicantsAppointedByMonth' => $regionApplicantsAppointedByMonth,
        ];
    }
}
