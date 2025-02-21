<?php

namespace App\Http\Controllers\Reports;

use Carbon\Carbon;
use App\Models\Type;
use App\Models\User;
use App\Models\Store;
use App\Models\Brand;
use App\Models\Region;
use App\Models\Division;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Exports\VacanciesExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Services\DataService\Reports\VacanciesReportDataService;

class VacanciesReportController extends Controller
{
    protected $vacanciesReportDataService;

    /**
     * Constructor method to initialize services and apply middleware.
     *
     * @param VacanciesReportDataService $vacanciesReportDataService
     * @return void
     */
    public function __construct(
        VacanciesReportDataService $vacanciesReportDataService
    ) {
        // Apply 'auth' and 'verified' middleware to ensure user is authenticated and verified
        $this->middleware(['auth', 'verified']);

        // Inject required services
        $this->vacanciesReportDataService = $vacanciesReportDataService;
    }

    /**
     * Display the reports dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (view()->exists('reports/vacancies')) {
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

            // Step 1: Initialize vacancy data
            $totalVacancies = 0;
            $totalVacanciesFilled = 0;
            $totalVacanciesByMonth = [];
            $totalVacanciesFilledByMonth = [];
            $totalVacanciesTypeByMonth = [];
            $totalVacanciesByType = [];

            // Check if the type is active
            if ($type !== null) {
                // Step 1: Fetch vacancy data from VacanciesReportDataService
                $totalVacancies = $this->vacanciesReportDataService->getTotalVacancies($type, $id, $startDate, $endDate);
                $totalVacanciesFilled = $this->vacanciesReportDataService->getTotalVacanciesFilled($type, $id, $startDate, $endDate);
                $totalVacanciesByMonth = $this->vacanciesReportDataService->getTotalVacanciesByMonth($type, $id, $startDate, $endDate);
                $totalVacanciesFilledByMonth = $this->vacanciesReportDataService->getTotalVacanciesFilledByMonth($type, $id, $startDate, $endDate);
                $totalVacanciesTypeByMonth = $this->vacanciesReportDataService->getTotalVacanciesTypeByMonth($type, $id, $startDate, $endDate);
                $totalVacanciesByType = $this->vacanciesReportDataService->getTotalVacanciesByType($type, $id, $startDate, $endDate);
            }

            //Positions logic based on user role and brand
            $positions = collect(); // Default to an empty collection

            if (in_array($authUser->role_id, [1, 2])) {
                // If role_id is 1 or 2, get all positions where id > 1
                $positions = Position::where('id', '>', 1)->get();
            } elseif ($authUser->role_id == 3) {
                // If role_id is 3, get all positions where brand_id is in the stores matching region_id
                $storeBrandIds = Store::where('region_id', $authUser->region_id)
                    ->pluck('brand_id'); // Get the brand_ids of all stores in the user's region

                // If $storeBrandIds contains 3 or 4 and does not contain 2, add 2
                if ($storeBrandIds->contains(3) || $storeBrandIds->contains(4)) {
                    $storeBrandIds->push(2);
                }

                // Now get all positions where brand_id is in the store's brand_ids
                $positions = Position::whereIn('brand_id', $storeBrandIds)
                    ->get();
            } elseif ($authUser->role_id == 4) {
                // If role_id is 4, get all positions where brand_id is in the stores matching division_id
                $storeBrandIds = Store::where('division_id', $authUser->division_id)
                    ->pluck('brand_id'); // Get the brand_ids of all stores in the user's division

                // If $storeBrandIds contains 3 or 4 and does not contain 2, add 2
                if ($storeBrandIds->contains(3) || $storeBrandIds->contains(4)) {
                    $storeBrandIds->push(2);
                }

                // Now get all positions where brand_id is in the store's brand_ids
                $positions = Position::whereIn('brand_id', $storeBrandIds)
                    ->get();
            } elseif ($authUser->role_id == 6) {
                // If role_id is 6, check if the user has a brand_id
                if ($authUser->brand_id) {
                    // If $authUser->brand_id is 2, 3, or 4, get positions where brand_id is 2
                    if (in_array($authUser->brand_id, [2, 3, 4])) {
                        $positions = Position::where('brand_id', 2)->get();
                    } else {
                        // Otherwise, get positions where brand_id matches the user's brand_id
                        $positions = Position::where('brand_id', $authUser->brand_id)->get();
                    }
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
                    ->where('division_id', $authUser->division_id)
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

            // Users
            $users = User::whereHas('vacancies')->get();

            // Types
            $types = Type::distinct('type_id')->get();

            // Return the 'reports/vacancies' view with the provided data
            return view('reports/vacancies', [
                'authUser' => $authUser,
                'totalVacancies' => $totalVacancies,
                'totalVacanciesFilled' => $totalVacanciesFilled,
                'totalVacanciesByMonth' => $totalVacanciesByMonth,
                'totalVacanciesFilledByMonth' => $totalVacanciesFilledByMonth,
                'totalVacanciesTypeByMonth' => $totalVacanciesTypeByMonth,
                'totalVacanciesByType' => $totalVacanciesByType,
                'positions' => $positions,
                'brands' => $brands,
                'divisions' => $divisions,
                'regions' => $regions,
                'stores' => $stores,
                'users' => $users,
                'types' => $types,
            ]);
        }

        // If the view 'reports/vacancies' does not exist, return a 404 error page
         return view('404');
    }

    /**
     * Update the vacancies reports dashboard data based on a selected filters.
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
                'position_id' => 'nullable|integer|exists:positions,id',
                'open_positions' => 'nullable|integer|min:0|max:10',
                'filled_positions' => 'nullable|integer|min:0|max:10|lte:open_positions',
                'brand_id' => 'nullable|integer|exists:brands,id',
                'division_id' => 'nullable|integer|exists:divisions,id',
                'region_id' => 'nullable|integer|exists:regions,id',
                'store_id' => 'nullable|array',
                'store_id.*' => 'integer|exists:stores,id',
                'user_id' => 'nullable|integer|exists:users,id',
                'type_id' => 'nullable|integer|exists:types,id',
                'unactioned' => 'nullable|string|in:Yes,No',
                'deleted' => 'nullable|string|in:Yes,No'
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

            // Extract filters by removing `_token`, `date`, and `search_terms` from the request
            $filters = Arr::except($request->all(), ['_token', 'date', 'search_terms']);

            // Initialize variables to 0 or empty before the null check

            // Step 1: Initialize vacancy data
            $totalVacancies = 0;
            $totalVacanciesFiltered = 0;
            $totalVacanciesFilledFiltered = 0;
            $totalVacanciesByMonthFiltered = [];
            $totalVacanciesByTypeFiltered = [];

            // Check if the type is active
            if ($type !== null) {
                // Step 1: Fetch vacancy data from VacanciesReportDataService
                $totalVacancies = $this->vacanciesReportDataService->getTotalVacancies($type, $id, $startDate, $endDate);
                $totalVacanciesFiltered = $this->vacanciesReportDataService->getTotalVacanciesFiltered($type, $id, $startDate, $endDate, $filters);
                $totalVacanciesFilledFiltered = $this->vacanciesReportDataService->getTotalVacanciesFilledFiltered($type, $id, $startDate, $endDate, $filters);
                $totalVacanciesByMonthFiltered = $this->vacanciesReportDataService->getTotalVacanciesByMonthFiltered($type, $id, $startDate, $endDate, $filters);
                $totalVacanciesByTypeFiltered = $this->vacanciesReportDataService->getTotalVacanciesByTypeFiltered($type, $id, $startDate, $endDate, $filters);
            }

            //Data to return
            $data = [
                'totalVacancies' => $totalVacancies,
                'totalVacanciesFiltered' => $totalVacanciesFiltered,
                'totalVacanciesFilledFiltered' => $totalVacanciesFilledFiltered,
                'totalVacanciesByMonthFiltered' => $totalVacanciesByMonthFiltered,
                'totalVacanciesByTypeFiltered' => $totalVacanciesByTypeFiltered
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
     * Export filtered vacancies data to an Excel report.
     *
     * This method retrieves vacancy data based on selected filters
     * and exports it as an Excel file. The filters include various
     * vacancy attributes, date range, position,
     * and type (e.g., store, division, or region).
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
        return Excel::download(new VacanciesExport($type, $id, $startDate, $endDate, $filters), 'Vacancies Report.xlsx');
    }
}
