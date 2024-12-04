<?php

namespace App\Http\Controllers\Reports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Race;
use App\Models\Store;
use App\Models\State;
use App\Models\Gender;
use App\Models\Region;
use App\Models\Division;
use App\Models\Setting;
use App\Models\Duration;
use App\Models\Education;
use App\Models\Shortlist;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Exports\ApplicantsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use App\Services\DataService\Reports\ApplicantsReportDataService;

class ApplicantsReportController extends Controller
{
    protected $applicantsReportDataService;

    /**
     * Constructor method to initialize services and apply middleware.
     *
     * @param ApplicantsReportDataService $applicantsReportDataService
     * @return void
     */
    public function __construct(
        ApplicantsReportDataService $applicantsReportDataService
    ) {
        // Apply 'auth' and 'verified' middleware to ensure user is authenticated and verified
        $this->middleware(['auth', 'verified']);

        // Inject required services
        $this->applicantsReportDataService = $applicantsReportDataService;
    }

    /**
     * Display the reports dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Check if the 'reports/applicants' view exists
        if (view()->exists('reports/applicants')) {
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

            // Initialize variables to 0 or empty before the null check

            // Step 1: Initialize applicant data
            $totalApplicants = 0;
            $totalAppointedApplicants = 0;
            $totalApplicantsByMonth = [];
            $totalApplicantsAppointedByMonth = [];
            $totalApplicantsGenderByMonth = [];
            $totalApplicantsRaceByMonth = [];

            // Check if the type is active
            if ($type !== null) {
                // Step 1: Fetch applicant data from ApplicantsReportDataService
                $totalApplicants = $this->applicantsReportDataService->getTotalApplicants($type, $id, $startDate, $endDate);
                $totalAppointedApplicants = $this->applicantsReportDataService->getTotalAppointedApplicants($type, $id, $startDate, $endDate);
                $totalApplicantsByMonth = $this->applicantsReportDataService->getTotalApplicantsByMonth($type, $id, $startDate, $endDate, $maxDistanceFromStore);
                $totalApplicantsAppointedByMonth = $this->applicantsReportDataService->getTotalApplicantsAppointedByMonth($type, $id, $startDate, $endDate);
                $totalApplicantsGenderByMonth = $this->applicantsReportDataService->getTotalApplicantsGenderByMonth($type, $id, $startDate, $endDate, $maxDistanceFromStore);
                $totalApplicantsRaceByMonth = $this->applicantsReportDataService->getTotalApplicantsRaceByMonth($type, $id, $startDate, $endDate, $maxDistanceFromStore);
            }

            // Genders
            $genders = Gender::all();

            // Races
            $races = Race::all();

            //Educations
            $educations = Education::all();

            // Experiences
            $experiences = Duration::all();

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
            } elseif ($authUser->role_id == 3 && $authUser->region_id) {
                // If role_id is 3, get all regions where id = authUser->region_id
                $regions = Region::where('id', $authUser->region_id)->get();
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

            // Return the 'reports/applicants' view with the calculated data
            return view('reports/applicants', [
                'totalApplicants' => $totalApplicants,
                'totalAppointedApplicants' => $totalAppointedApplicants,
                'totalApplicantsByMonth' => $totalApplicantsByMonth,
                'totalApplicantsAppointedByMonth' => $totalApplicantsAppointedByMonth,
                'totalApplicantsGenderByMonth' => $totalApplicantsGenderByMonth,
                'totalApplicantsRaceByMonth' => $totalApplicantsRaceByMonth,
                'genders' => $genders,
                'races' => $races,
                'educations' => $educations,
                'experiences' => $experiences,
                'divisions' => $divisions,
                'regions' => $regions,
                'stores' => $stores,
            ]);
        }

        // If the view 'admin/home' does not exist, return a 404 error page
        return view('404');
    }

    /**
     * Update the applicants reports dashboard data based on a selected filters.
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
                'gender_id' => 'nullable|integer|exists:genders,id',
                'race_id' => 'nullable|integer|exists:races,id',
                'min_age' => 'nullable|integer|min:18|max:80',
                'max_age' => 'nullable|integer|min:18|max:80|gte:min_age',
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

            // Step 1: Initialize applicant data
            $totalApplicants = 0;
            $totalAppointedApplicants = 0;
            $totalApplicantsFiltered = 0;
            $totalAppointedApplicantsFiltered = 0;
            $totalApplicantsByMonthFiltered = [];

            // Check if the type is active
            if ($type !== null) {
                // Step 1: Fetch applicant data from ApplicantsReportDataService
                $totalApplicants = $this->applicantsReportDataService->getTotalApplicants($type, $id, $startDate, $endDate);
                $totalAppointedApplicants = $this->applicantsReportDataService->getTotalAppointedApplicants($type, $id, $startDate, $endDate);
                $totalApplicantsFiltered = $this->applicantsReportDataService->getTotalApplicantsFiltered($type, $id, $startDate, $endDate, $filters);
                $totalAppointedApplicantsFiltered = $this->applicantsReportDataService->getTotalAppointedApplicantsFiltered($type, $id, $startDate, $endDate, $filters);
                $totalApplicantsByMonthFiltered = $this->applicantsReportDataService->getTotalApplicantsByMonthFiltered($type, $id, $startDate, $endDate, $maxDistanceFromStore, $filters);
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
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Export filtered applicants data to an Excel report.
     *
     * This method retrieves applicant data based on selected filters
     * and exports it as an Excel file. The filters include various
     * applicant attributes, date range, location-based proximity,
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

        // Retrieve the maximum proximity distance in kilometers for filtering applicants, default to 50km
        $maxDistanceFromStore = $request->input('maxDistanceFromStore', 50);

        // Retrieve all filters from the request, excluding '_token', 'date', and 'search_terms'
        $filters = $request->except(['_token', 'date', 'search_terms']);

        // Export data to an Excel file, passing filters, type, id, date range, and proximity
        return Excel::download(new ApplicantsExport($type, $id, $startDate, $endDate, $maxDistanceFromStore, $filters), 'Applicants Report.xlsx');
    }
}
