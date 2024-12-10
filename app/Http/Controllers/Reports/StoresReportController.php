<?php

namespace App\Http\Controllers\Reports;

use Carbon\Carbon;

use App\Models\User;
use App\Models\Town;
use App\Models\Brand;
use App\Models\Store;
use App\Models\Region;
use App\Models\Division;
use App\Models\Province;
use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exports\StoresOverTimeExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Exports\StoresExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use App\Services\DataService\Reports\StoresReportDataService;

class StoresReportController extends Controller
{
    protected $storesReportDataService;

    /**
     * Constructor method to initialize services and apply middleware.
     *
     * @param StoresReportDataService $StoresReportDataService
     * @return void
     */
    public function __construct(
        StoresReportDataService $storesReportDataService
    ) {
        // Apply 'auth' and 'verified' middleware to ensure user is authenticated and verified
        $this->middleware(['auth', 'verified']);

        // Inject required services
        $this->storesReportDataService = $storesReportDataService;
    }

    /**
     * Display the reports dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Check if the 'reports/stores' view exists
        if (view()->exists('reports/stores')) {
            // Retrieve the ID of the currently authenticated user
            $authUserId = Auth::id();

            // Fetch the authenticated user
            $authUser = User::find($authUserId);

            // Define the date range (from the start of the year to the end of today)
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfDay();

            // Set the type to 'all' to filter all vacancies
            $type = 'all';
            $id = null;

            // Set the $type based on the role_id of $authUser
            if ($authUser->role_id == 3) {
                $type = 'region';
                $id = $authUser->region_id;
            } elseif ($authUser->role_id == 4 || $authUser->role_id == 5) {
                $type = 'division';
                $id = $authUser->devision_id;
            } elseif ($authUser->role_id == 6) {
                $type = 'store';
                $id = $authUser->store_id;
            }

            // Get the max proximity from store
            $maxDistanceFromStore = Setting::where('key', 'max_distance_from_store')->first()->value ?? 50;

            // Step 1: Initialize store data
            $averageTimeToShortlist = 0;
            $averageTimeToHire = 0;
            $totalApplicantsAppointed = 0;
            $talentPoolApplicants = 0;
            $totalApplicantsSaved = 0;
            $averageDistanceApplicantsAppointed = 0;
            $averageAssessmentScoreApplicantsAppointed = 0;
            $totalInterviewsScheduled = 0;
            $totalInterviewsCompleted = 0;
            $hireToInterviewRatio = 0;
            $hireToInterviewRatioDisplay = 0;

            // Check if the type is active
            if ($type !== null) {
                // Step 1: Fetch store data from storesReportDataService
                $averageTimeToShortlist = $this->storesReportDataService->getAverageTimeToShortlist($type, $id, $startDate, $endDate);
                $averageTimeToHire = $this->storesReportDataService->getAverageTimeToHire($type, $id, $startDate, $endDate);
                $totalApplicantsAppointed = $this->storesReportDataService->getTotalApplicantsAppointed($type, $id, $startDate, $endDate);
                $talentPoolApplicants = $this->storesReportDataService->getTalentPoolApplicants($type, null, $startDate, $endDate, $maxDistanceFromStore);
                $totalApplicantsSaved = $this->storesReportDataService->getTotalApplicantsSaved($type, $id, $startDate, $endDate);
                $averageDistanceApplicantsAppointed = $this->storesReportDataService->getAverageDistanceApplicantsAppointed($type, $id, $startDate, $endDate);
                $averageAssessmentScoreApplicantsAppointed = $this->storesReportDataService->getAverageAssessmentScoreApplicantsAppointed($type, $id, $startDate, $endDate);
                $totalInterviewsScheduled = $this->storesReportDataService->getTotalInterviewsScheduled($type, null, $startDate, $endDate);
                $totalInterviewsCompleted = $this->storesReportDataService->getTotalInterviewsCompleted($type, $id, $startDate, $endDate);

                if ($totalApplicantsAppointed > 0) {
                    $ratio = $totalInterviewsCompleted / $totalApplicantsAppointed;
                    $hireToInterviewRatio = number_format($ratio, 1); // Only the numeric value
                    $hireToInterviewRatioDisplay = '1 to ' . $hireToInterviewRatio; // For readable display
                } else {
                    $hireToInterviewRatio = 0;
                    $hireToInterviewRatioDisplay = 0;
                }
            }

            // Brands logic
            $brands = collect(); // Default to an empty collection

            if (in_array($authUser->role_id, [1, 2])) {
                // If role_id is 1 or 2, get all brands where id > 1
                $brands = Brand::where('id', '>', 1)->get();
            } elseif ($authUser->role_id == 3) {
                // If role_id is 3, get all brands where id matches the brands in stores in the user's region
                $storeBrandIds = Store::where('region_id', $authUser->region_id)
                    ->pluck('brand_id'); // Get the brand_ids of all stores in the user's region
            
                // If $storeBrandIds contains 3 or 4 and does not contain 2, add 2
                if ($storeBrandIds->contains(3) || $storeBrandIds->contains(4)) {
                    $storeBrandIds->push(2);
                }
            
                // Get all brands where id is in the store's brand_ids
                $brands = Brand::whereIn('id', $storeBrandIds)->get();
            } elseif ($authUser->role_id == 4) {
                // If role_id is 4, get all brands where id matches the brands in stores in the user's division
                $storeBrandIds = Store::where('division_id', $authUser->division_id)
                    ->pluck('brand_id'); // Get the brand_ids of all stores in the user's division
            
                // If $storeBrandIds contains 3 or 4 and does not contain 2, add 2
                if ($storeBrandIds->contains(3) || $storeBrandIds->contains(4)) {
                    $storeBrandIds->push(2);
                }
            
                // Get all brands where id is in the store's brand_ids
                $brands = Brand::whereIn('id', $storeBrandIds)->get();
            } elseif ($authUser->role_id == 6) {
                // If role_id is 6, get all brands where id matches the brand of the users store
                $storeBrandIds = Store::where('id', $authUser->store_id)
                    ->pluck('brand_id'); // Get the brand_ids of all stores in the user's division
            
                // If $storeBrandIds contains 3 or 4 and does not contain 2, add 2
                if ($storeBrandIds->contains(3) || $storeBrandIds->contains(4)) {
                    $storeBrandIds->push(2);
                }
            
                // Get all brands where id is in the store's brand_ids
                $brands = Brand::whereIn('id', $storeBrandIds)->get();
            }

            // Provinces
            $provinces = Province::all();

            // Towns
            $towns = Town::all();

            // Divisions logic
            $divisions = collect(); // Default to an empty collection

            if (in_array($authUser->role_id, [1, 2])) {
                // If role_id is 1 or 2, get all divisions
                $divisions = Division::all();
            } elseif (($authUser->role_id == 4 || $authUser->role_id == 5) && $authUser->division_id) {
                // If role_id is 3, get all divisions where id = authUser->division_id
                $divisions = Division::where('id', $authUser->division_id)->get();
            }

            // Regions logic
            $regions = collect(); // Default to an empty collection

            if (in_array($authUser->role_id, [1, 2, 4, 5])) {
                // If role_id is 1 or 2, get all regions
                $regions = Region::all();
            } elseif (in_array($authUser->role_id, [3, 4, 5]) && $authUser->division_id) {
                // If role_id is 3 or 4 or 5, get all regions where division_id = authUser->division_id
                $regions = Region::where('division_id', $authUser->division_id)->get();
            }

            // Stores logic
            $stores = collect(); // Default to an empty collection

            if (in_array($authUser->role_id, [1, 2])) {
                // If role_id is 1 or 2, get all stores
                $stores = Store::with(['brand', 'town'])
                    ->get();
            } elseif ($authUser->role_id == 3) {
                // If role_id is 3, get all stores where region_id = user->region_id
                $stores = Store::with(['brand', 'town'])
                    ->where('region_id', $authUser->region_id)
                    ->get();
            } elseif ($authUser->role_id == 4 || $authUser->role_id == 5) {
                // If role_id is 4, get all stores where division_id = user->division_id
                $stores = Store::with(['brand', 'town'])
                    ->where('division_id', $authUser->division_id)
                    ->get();
            } elseif ($authUser->role_id == 6) {
                // Get stores where store_id is = user->store_id
                $stores = Store::with(['brand', 'town'])
                    ->where('id', $authUser->store_id)
                    ->get();
            }

            // Return the 'reports/stores' view with the calculated data
            return view('reports/stores', [
                'authUser' => $authUser,
                'averageTimeToShortlist' => $averageTimeToShortlist,
                'averageTimeToHire' => $averageTimeToHire,
                'totalApplicantsAppointed' => $totalApplicantsAppointed,
                'talentPoolApplicants' => $talentPoolApplicants,
                'totalApplicantsSaved' => $totalApplicantsSaved,
                'averageDistanceApplicantsAppointed' => $averageDistanceApplicantsAppointed,
                'averageAssessmentScoreApplicantsAppointed' => $averageAssessmentScoreApplicantsAppointed,
                'totalInterviewsScheduled' => $totalInterviewsScheduled,
                'totalInterviewsCompleted' => $totalInterviewsCompleted,
                'hireToInterviewRatio' => $hireToInterviewRatio,
                'hireToInterviewRatioDisplay' => $hireToInterviewRatioDisplay,
                'brands' => $brands,
                'provinces' => $provinces,
                'towns' => $towns,
                'divisions' => $divisions,
                'regions' => $regions,
                'stores' => $stores,
            ]);
        }

        // If the view 'reports/stores' does not exist, return a 404 error page
        return view('404');
    }

    /**
     * Update the stores reports dashboard data based on a selected filters.
     *
     * This method is triggered via an AJAX request and retrieves
     * updated statistics for the applicants reports on the selected
     * filters.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function update(Request $request)
    {
        try {
            // Validate Input
            $request->validate([
                'date' => 'required|string',
                'brand_id' => 'nullable|integer|exists:brands,id',
                'province_id' => 'nullable|integer|exists:provinces,id',
                'town_id' => 'nullable|integer|exists:towns,id',
                'division_id' => 'nullable|integer|exists:divisions,id',
                'region_id' => 'nullable|integer|exists:regions,id',
                'store_id' => 'nullable|array',
                'store_id.*' => 'integer|exists:stores,id',
            ]);

            // Retrieve the ID of the currently authenticated user
            $authUserId = Auth::id();

            // Fetch the authenticated user
            $authUser = User::find($authUserId);

            // Extract and parse the date range from the request
            $dateRange = $request->input('date'); // Assuming 'date' is the input field name in your form

            // Split the date range string into start and end dates
            [$startDateString, $endDateString] = explode(' to ', $dateRange);

            // Parse the start and end dates
            $startDate = Carbon::parse($startDateString)->startOfDay();
            $endDate = Carbon::parse($endDateString)->endOfDay();

            // Set the type to 'all' to filter all vacancies
            $type = 'all';
            $id = null;

            // Set the $type based on the role_id of $authUser
            if ($authUser->role_id == 3) {
                $type = 'region';
                $id = $authUser->region_id;
            } elseif ($authUser->role_id == 4 || $authUser->role_id == 5) {
                $type = 'division';
                $id = $authUser->devision_id;
            } elseif ($authUser->role_id == 6) {
                $type = 'store';
                $id = $authUser->store_id;
            }

            // Get the max proximity from store
            $maxDistanceFromStore = Setting::where('key', 'max_distance_from_store')->first()->value ?? 50;

            // Extract filters by removing `_token`, `date`, and `search_terms` from the request
            $filters = Arr::except($request->all(), ['_token', 'date', 'search_terms']);

            // Initialize variables to 0 or empty before the null check

            // Step 1: Initialize store data
            $averageTimeToShortlistFiltered = 0;
            $averageTimeToHireFiltered = 0;
            $totalApplicantsAppointed = 0;
            $totalApplicantsAppointedFiltered = 0;
            $totalApplicantsSaved = 0;
            $totalApplicantsSavedFiltered = 0;
            $averageDistanceApplicantsAppointedFiltered = 0;
            $averageAssessmentScoreApplicantsAppointedFiltered = 0;
            $totalInterviewsCompleted = 0;
            $totalInterviewsCompletedFiltered = 0;
            $hireToInterviewRatio = 0;
            $hireToInterviewRatioDisplay = 0;

            // Check if the type is active
            if ($type !== null) {
                // Step 1: Fetch store data from storesReportDataService
                $averageTimeToShortlistFiltered = $this->storesReportDataService->getAverageTimeToShortlistFiltered($type, $id, $startDate, $endDate, $filters);
                $averageTimeToHireFiltered = $this->storesReportDataService->getAverageTimeToHireFiltered($type, $id, $startDate, $endDate, $filters);
                $totalApplicantsAppointed = $this->storesReportDataService->getTotalApplicantsAppointed($type, $id, $startDate, $endDate);
                $totalApplicantsAppointedFiltered = $this->storesReportDataService->getTotalApplicantsAppointedFiltered($type, $id, $startDate, $endDate, $filters);
                $totalApplicantsSaved = $this->storesReportDataService->getTotalApplicantsSaved($type, $id, $startDate, $endDate);
                $totalApplicantsSavedFiltered = $this->storesReportDataService->getTotalApplicantsSavedFiltered($type, $id, $startDate, $endDate, $filters);
                $averageDistanceApplicantsAppointedFiltered = $this->storesReportDataService->getAverageDistanceApplicantsAppointedFiltered($type, $id, $startDate, $endDate, $filters);
                $averageAssessmentScoreApplicantsAppointedFiltered = $this->storesReportDataService->getAverageAssessmentScoreApplicantsAppointedFiltered($type, $id, $startDate, $endDate, $filters);
                $totalInterviewsCompleted = $this->storesReportDataService->getTotalInterviewsCompleted($type, $id, $startDate, $endDate);
                $totalInterviewsCompletedFiltered = $this->storesReportDataService->getTotalInterviewsCompletedFiltered($type, $id, $startDate, $endDate, $filters);

                if ($totalApplicantsAppointedFiltered > 0) {
                    $ratio = $totalInterviewsCompletedFiltered / $totalApplicantsAppointedFiltered;
                    $hireToInterviewRatio = number_format($ratio, 1); // Only the numeric value
                    $hireToInterviewRatioDisplay = '1 to ' . $hireToInterviewRatio; // For readable display
                } else {
                    $hireToInterviewRatio = 0;
                    $hireToInterviewRatioDisplay = 0;
                }
            }

            //Data to return
            $data = [
                'averageTimeToShortlistFiltered' => $averageTimeToShortlistFiltered,
                'averageTimeToHireFiltered' => $averageTimeToHireFiltered,
                'totalApplicantsAppointed' => $totalApplicantsAppointed,
                'totalApplicantsAppointedFiltered' => $totalApplicantsAppointedFiltered,
                'totalApplicantsSaved' => $totalApplicantsSaved,
                'totalApplicantsSavedFiltered' => $totalApplicantsSavedFiltered,
                'averageDistanceApplicantsAppointedFiltered' => $averageDistanceApplicantsAppointedFiltered,
                'averageAssessmentScoreApplicantsAppointedFiltered' => $averageAssessmentScoreApplicantsAppointedFiltered,
                'totalInterviewsCompleted' => $totalInterviewsCompleted,
                'totalInterviewsCompletedFiltered' => $totalInterviewsCompletedFiltered,
                'hireToInterviewRatio' => $hireToInterviewRatio,
                'hireToInterviewRatioDisplay' => $hireToInterviewRatioDisplay,
            ];

            // Return the updated data as JSON
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Data updated successfully!'
            ]);
        } catch (ValidationException $e) {
            // Catch validation errors and return them in a structured JSON response
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors() // This will return the validation errors in a key-value format
            ], 422); // 422 Unprocessable Entity is standard for validation errors
        } catch (\Exception $e) {
            // Return other errors
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Export filtered stores data to an Excel report.
     *
     * This method retrieves stores data based on selected filters
     * and exports it as an Excel file. The filters include various
     * vacancy attributes, date range, brand,
     * and store (e.g., store, division, or region).
     *
     * @param Request $request The incoming HTTP request containing filters.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse An Excel file download response.
     */
    public function export(Request $request)
    {
        // Retrieve the ID of the currently authenticated user
        $authUserId = Auth::id();

        // Fetch the authenticated user
        $authUser = User::find($authUserId);

        // Set the type to 'all' to filter all vacancies
        $type = 'all';
        $id = null;

        // Set the $type based on the role_id of $authUser
        if ($authUser->role_id == 3) {
            $type = 'region';
            $id = $authUser->region_id;
        } elseif ($authUser->role_id == 4 || $authUser->role_id == 5) {
            $type = 'division';
            $id = $authUser->devision_id;
        } elseif ($authUser->role_id == 6) {
            $type = 'store';
            $id = $authUser->store_id;
        }

        // Extract and parse the date range from the request
        $dateRange = $request->input('date'); // Assuming 'date' is the input field name in your form

        // Split the date range string into start and end dates
        [$startDateString, $endDateString] = explode(' to ', $dateRange);

        // Parse the start and end dates
        $startDate = Carbon::parse($startDateString)->startOfDay();
        $endDate = Carbon::parse($endDateString)->endOfDay();

        // Retrieve all filters from the request, excluding '_token', 'date', and 'search_terms'
        $filters = $request->except(['_token', 'date', 'search_terms']);

        // Export data to an Excel file, passing filters, type, id, date range, and proximity
        return Excel::download(new StoresExport($type, $id, $startDate, $endDate, $filters), 'Stores Report.xlsx');
    }
}
