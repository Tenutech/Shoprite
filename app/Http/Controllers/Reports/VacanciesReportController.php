<?php

namespace App\Http\Controllers\Reports;

use Carbon\Carbon;
use App\Models\Type;
use App\Models\User;
use App\Models\Store;
use App\Models\Status;
use App\Models\Setting;
use App\Models\Vacancy;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Controllers\Controller;
use App\Exports\VacancyTypesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use App\Exports\VacanciesOverTimeExport;
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

            // Step 1: Initialize appliacnt data
            $totalVacancies = 0;
            $totalVacanciesFilled = 0;
            $totalVacanciesByMonth = [];
            $totalVacanciesFilledByMonth = [];
            $totalVacanciesTypeByMonth = [];
            $totalVacanciesByType = [];

            // Check if the type is active
            if ($type !== null) {
                // Step 1: Fetch applicant data from VacanciesReportDataService
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

            //Stores logic
            $stores = collect(); // Default to an empty collection

            if (in_array($authUser->role_id, [1, 2])) {
                // If role_id is 1 or 2, get all stores where id > 1
                $stores = Store::with(['brand', 'town'])
                    ->get();
            } elseif ($authUser->role_id == 3) {
                // If role_id is 3, get all stores where region_id = user->region_id
                $stores = Store::with(['brand', 'town'])
                    ->where('region_id', $authUser->region_id)
                    ->get();
            } elseif ($authUser->role_id == 4) {
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
            $authUsers = User::whereHas('vacancies')->get();

            // Types
            $types = Type::distinct('type_id')->get();

            // Return the 'reports/vacancies' view with the provided data
            return view('reports/vacancies', [
                'totalVacancies' => $totalVacancies,
                'totalVacanciesFilled' => $totalVacanciesFilled,
                'totalVacanciesByMonth' => $totalVacanciesByMonth,
                'totalVacanciesFilledByMonth' => $totalVacanciesFilledByMonth,
                'totalVacanciesTypeByMonth' => $totalVacanciesTypeByMonth,
                'totalVacanciesByType' => $totalVacanciesByType,
                'positions' => $positions,
                'stores' => $stores,
                'users' => $authUsers,
                'types' => $types,
            ]);
        }

        // If the view 'reports/vacancies' does not exist, return a 404 error page
         return view('404');
    }

    /**
     * Update the appliacnts reports dashboard data based on a selected filters.
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
                'open_positions' => 'nullable|integer|min:1|max:10',
                'filled_positions' => 'nullable|integer|min:1|max:10',
                'education_id' => 'nullable|integer|exists:educations,id',
                'duration_id' => 'nullable|integer|exists:durations,id',
                'min_literacy' => 'nullable|integer|min:0|max:10',
                'max_literacy' => 'nullable|integer|min:0|max:10|gte:min_literacy',
                'min_numeracy' => 'nullable|integer|min:0|max:10',
                'max_numeracy' => 'nullable|integer|min:0|max:10|gte:min_numeracy',
                'min_situational' => 'nullable|integer|min:0|max:10',
                'max_situational' => 'nullable|integer|min:0|max:10|gte:min_situational',
                'min_overall' => 'nullable|numeric|min:0|max:5',
                'max_overall' => 'nullable|numeric|min:0|max:5|gte:min_overall',
                'employment' => 'nullable|string|in:A,B,I,P,N',
                'completed' => 'nullable|string|in:Yes,No',
                'shortlisted' => 'nullable|string|in:Yes,No',
                'interviewed' => 'nullable|string|in:Yes,No',
                'appointed' => 'nullable|string|in:Yes,No',
                'store_id' => 'nullable|integer|exists:stores,id',
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

            // Step 1: Initialize appliacnt data
            $totalApplicants = 0;
            $totalAppointedApplicants = 0;
            $totalApplicantsFiltered = 0;
            $totalAppointedApplicantsFiltered = 0;
            $totalApplicantsByMonthFiltered = [];

            // Check if the type is active
            if ($type !== null) {
                // Step 1: Fetch applicant data from ApplicantsReportDataService
                $totalApplicants = $this->vacanciesReportDataService->getTotalApplicants($type, $id, $startDate, $endDate);
                $totalAppointedApplicants = $this->vacanciesReportDataService->getTotalAppointedApplicants($type, $id, $startDate, $endDate);
                $totalApplicantsFiltered = $this->vacanciesReportDataService->getTotalApplicantsFiltered($type, $id, $startDate, $endDate, $filters);
                $totalAppointedApplicantsFiltered = $this->vacanciesReportDataService->getTotalAppointedApplicantsFiltered($type, $id, $startDate, $endDate, $filters);
                $totalApplicantsByMonthFiltered = $this->vacanciesReportDataService->getTotalApplicantsByMonthFiltered($type, $id, $startDate, $endDate, $maxDistanceFromStore, $filters);
            }

            //Data to return
            $data = [
                'totalApplicants' => $totalApplicants,
                'totalAppointedApplicants' => $totalAppointedApplicants,
                'totalApplicantsFiltered' => $totalApplicantsFiltered,
                'totalAppointedApplicantsFiltered' => $totalAppointedApplicantsFiltered,
                'totalApplicantsByMonthFiltered' => $totalApplicantsByMonthFiltered
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
                'message' => 'Failed to retrieve data!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Summary of getData
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        // Use the default date range if not provided
        $filters = [
            'position_id' => $request->position_id,
            'store_id' => $request->store_id,
            'user_id' => $request->user_id,
            'type_id' => $request->type_id,
            'filled_positions' => $request->filled_positions,
            'start_date' => $request->start_date ?? Carbon::now()->startOfYear()->toDateString(),
            'end_date' => $request->end_date ?? Carbon::now()->toDateString(),
        ];

        $vacancies = $this->vacanciesReportDataService->getFilteredVacancies($filters);
        $chartData = $this->vacanciesReportDataService->prepareChartData($vacancies, $filters['start_date'], $filters['end_date']);

        $authUsers = User::whereHas('vacancies', function ($query) use ($filters) {
            $this->vacanciesReportDataService->applyFilters($query, $filters);
        })->get();

        $stores = Store::whereHas('vacancies', function ($query) use ($filters) {
            $this->vacanciesReportDataService->applyFilters($query, $filters);
        })->get();

        $positions = Position::whereHas('vacancies', function ($query) use ($filters) {
            $this->vacanciesReportDataService->applyFilters($query, $filters);
        })->get();

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully!',
            'chartData' => $chartData,
            'filters' => [
                'positions' => $positions,
                'stores' => $stores,
                'users' => $authUsers,
            ],
        ]);
    }
    public function exportVacancyTypes(Request $request)
    {
        $filters = $this->getFiltersFromRequest($request);
        return Excel::download(new VacancyTypesExport($filters), 'Vacancy Types.xlsx');
    }

    public function exportVacanciesOverTime(Request $request)
    {
        $filters = $this->getFiltersFromRequest($request);
        return Excel::download(new VacanciesOverTimeExport($filters), 'Vacancies Over Time.xlsx');
    }

    private function getFiltersFromRequest(Request $request)
    {
        $dateRange = explode(' to ', $request->get('date_range', ''));
        return [
            'position_id' => $request->position_id,
            'store_id' => $request->store_id,
            'user_id' => $request->user_id,
            'type_id' => $request->type_id,
            'filled_positions' => $request->filled_positions,
            'start_date' => $dateRange[0] ?? null,
            'end_date' => $dateRange[1] ?? null,
        ];
    }
}
