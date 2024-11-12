<?php

namespace App\Http\Controllers\Reports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Race;
use App\Models\Store;
use App\Models\State;
use App\Models\Gender;
use App\Models\Setting;
use App\Models\Duration;
use App\Models\Education;
use App\Models\Shortlist;
use Illuminate\Http\Request;
use App\Models\ChatTemplate;
use App\Models\ReminderSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
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
     * Display the admin dashboard.
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

            // Step 1: Initialize appliacnt data
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
            
            // Stores
            $stores = Store::all(); 

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
                'stores' => $stores,
            ]);
        }

        // If the view 'admin/home' does not exist, return a 404 error page
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

            // Initialize variables to 0 or empty before the null check

            // Step 1: Initialize appliacnt data
            $totalApplicantsFiltered = 0;
            $totalAppointedApplicantsFiltered = 0;
            $totalApplicantsByMonthFiltered = [];

            // Check if the type is active
            if ($type !== null) {
                // Step 1: Fetch applicant data from ApplicantsReportDataService
                $totalApplicantsFiltered = $this->applicantsReportDataService->getTotalApplicantsFiltered($type, $id, $startDate, $endDate);
                $totalAppointedApplicantsFiltered = $this->applicantsReportDataService->getTotalAppointedApplicantsFiltered($type, $id, $startDate, $endDate);
                $totalApplicantsByMonthFiltered = $this->applicantsReportDataService->getTotalApplicantsByMonthFiltered($type, $id, $startDate, $endDate, $maxDistanceFromStore);
            }

            //Data to return
            $data = [
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
