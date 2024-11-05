<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Division;
use App\Models\Province;
use App\Models\Region;
use App\Models\Store;
use App\Models\Town;
use App\Services\DataService\Reports\StoreDataService;
use App\Exports\StoresOverTimeExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class StoresController extends Controller
{
    /**
     * Constructor method to initialize services and apply middleware.
     *
     * @param ApplicantDataService $applicantDataService
     * @param ApplicantProximityService $applicantProximityService
     * @param VacancyDataService $vacancyDataService
     * @return void
     */
    public function __construct(
        StoreDataService $storeDataService,
    ) {
        // Apply 'auth' and 'verified' middleware to ensure user is authenticated and verified
        $this->middleware(['auth', 'verified']);

        // Inject required services
        $this->storeDataService = $storeDataService;
    }

    /**
     * Show the stores report.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (view()->exists('admin/home')) {
            $filters = [];

            $stores = Store::all();
            $brands = Brand::all();
            $towns = Town::all();
            $regions = Region::all();
            $provinces = Province::all();
            $divisions = Division::all();

            // Define the date range (from the start of the year to the end of today)
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfDay();

            $filters = [
                'from' => $startDate,
                'to' => $endDate,
            ];

            $data = $this->fetchData($filters);

            $data = array_merge($data, [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'stores' => $stores,
                'brands' => $brands,
                'towns' => $towns,
                'regions' => $regions,
                'provinces' => $provinces,
                'divisions' => $divisions,
            ]);

            return view('admin/reports/stores/index', $data);
        }
        return view('404');
    }

    /**
     * Update the report based on filters
     *
     * This method is triggered via an AJAX request and retrieves
     * updated statistics for the admin dashboard, including vacancy,
     * interview, applicant, and proximity data based on the selected
     * date range (startDate to endDate).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function updateData(Request $request)
    {
        try {
            $filters = [];

            // Define the date range (from the start of the year to the end of today)
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfDay();

            $filters = [
                'store_id' => $request->store_id,
                'brand_id' => $request->brand_id,
                'town_id' => $request->town_id,
                'province_id' => $request->province_id,
                'region_id' => $request->region_id,
                'division_id' => $request->division_id,
                'start_date' => $request->start_date ?? Carbon::now()->startOfYear()->toDateString(),
                'end_date' => $request->end_date ?? Carbon::now()->toDateString(),
            ];

            $data = $this->fetchData($filters);

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
     * @param \Carbon\Carbon $startDate The start date of the range.
     * @param \Carbon\Carbon $endDate The end date of the range.
     * @return array An associative array containing region metrics.
     */
    private function fetchData($filters)
    {
        $totalApplicantsPlaced = $this->storeDataService->getTotalCompletedApplicants($filters);
        $averageTimetoHire = $this->storeDataService->getAverageTimeToHire($filters);
        $averageAssementScore = $this->storeDataService->getAverageAssessmentScoreApplicantsAppointed($filters);
        $averageDistanceApplicantsAppointed = $this->storeDataService->getAverageDistanceApplicantsAppointed($filters);
        $shortlistToHireRatio = $this->storeDataService->getShortlistToHireRatio($filters);
        $interviewToHireRatio = $this->storeDataService->getinterviewToHireRatio($filters);

        return [
            'totalApplicantsPlaced' => $totalApplicantsPlaced,
            'averageTimetoHire' => $averageTimetoHire,
            'averageAssementScore' => $averageAssementScore,
            'averageDistanceApplicantsAppointed' => $averageDistanceApplicantsAppointed,
            'shortlistToHireRatio' => $shortlistToHireRatio,
            'interviewToHireRatio' => $interviewToHireRatio,
        ];
    }

    public function export(Request $request)
    {
        $filters = $this->getFiltersFromRequest($request);
        return Excel::download(new StoresOverTimeExport($filters, $this->storeDataService), 'stores_over_time.xlsx');
    }

    private function getFiltersFromRequest(Request $request)
    {
        $dateRange = explode(' to ', $request->get('date_range', ''));
        return [
            'store_id' => $request->store_id,
            'brand_id' => $request->brand_id,
            'town_id' => $request->town_id,
            'province_id' => $request->province_id,
            'region_id' => $request->region_id,
            'division_id' => $request->division_id,
            'start_date' => $dateRange[0] ?? null,
            'end_date' => $dateRange[1] ?? null,
        ];
    }
}
