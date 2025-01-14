<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Shortlist;
use App\Models\Setting;
use App\Models\User;
use App\Models\State;
use App\Models\ChatTemplate;
use App\Services\DataService\ApplicantDataService;
use App\Services\DataService\ApplicantProximityService;
use App\Services\DataService\VacancyDataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DataController extends Controller
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
     * Retrieve common data for metrics calculations.
     *
     * This method fetches the authenticated user, determines the date range,
     * and sets the type and ID based on the user's role.
     *
     * @return array An array containing authUser, startDate, endDate, type, and id.
     */
    protected function getCommonMetricsData()
    {
        // Retrieve the ID of the currently authenticated user
        $authUserId = Auth::id();

        // Fetch the authenticated user
        $authUser = User::find($authUserId);

        // Define the date range (from the start of the year to the end of today)
        $startDate = Carbon::now()->subYear()->startOfMonth();
        $endDate = Carbon::now()->endOfDay();

        // Determine the $type and $id based on the user's role
        $type = null;
        $id = null;

        if ($authUser) {
            if (in_array($authUser->role_id, [1, 2])) {
                $type = 'all';
            } elseif ($authUser->role_id == 3) {
                $type = 'region';
                $id = $authUser->region_id;
            } elseif (in_array($authUser->role_id, [4, 5])) {
                $type = 'division';
                $id = $authUser->division_id;
            } elseif ($authUser->role_id == 6) {
                $type = 'store';
                $id = $authUser->store_id;
            }
        }

        // Get the max proximity from store
        $maxDistanceFromStore = Setting::where('key', 'max_distance_from_store')->first()->value ?? 50;

        return [
            'authUser' => $authUser,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'maxDistanceFromStore' => $maxDistanceFromStore,
            'type' => $type,
            'id' => $id
        ];
    }

    /**
     * Retrieve time-related metrics for vacancies.
     *
     * This method calculates and returns key metrics related to vacancy management,
     * including the average time to shortlist, the average time to hire, and the adoption rate
     * (percentage of vacancies filled). The data is returned as a JSON response.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getTimeMetrics(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range (from the start of the year to the end of today)
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Initialize variables to 0 or empty before the null check

        // Initialize vacancy data
        $totalVacancies = 0;
        $totalVacanciesFilled = 0;

        // Initialize time data
        $averageTimeToShortlist = 0;
        $averageTimeToHire = 0;
        $adoptionRate = 0;

        // Check if the authenticated user is active
        if ($type !== null) {
            // Fetch vacancy data from VacancyDataService
            $totalVacancies = $this->vacancyDataService->getTotalVacancies($type, $id, $startDate, $endDate);
            $totalVacanciesFilled = $this->vacancyDataService->getTotalVacanciesFilled($type, $id, $startDate, $endDate);

            // Fetch time data from VacancyDataService
            $averageTimeToShortlist = $this->vacancyDataService->getAverageTimeToShortlist($type, $id, $startDate, $endDate);
            $averageTimeToHire = $this->vacancyDataService->getAverageTimeToHire($type, $id, $startDate, $endDate);
            $adoptionRate = ($totalVacancies > 0) ? round($totalVacanciesFilled / $totalVacancies * 100) : 0;
        }

        // Return the calculated metrics as a JSON response:
        return response()->json([
            'averageTimeToShortlist' => $averageTimeToShortlist,
            'averageTimeToHire' => $averageTimeToHire,
            'adoptionRate' => $adoptionRate
        ]);
    }

    /**
     * Retrieve proximity-related metrics for vacancies.
     *
     * This method calculates and returns key metrics related to proximity,
     * including the average distance of the talent pool and successful placements
     * from the store. The data is returned as a JSON response.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getProximityMetrics(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range (from the start of the year to the end of today)
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Get the max proximity from store
        $maxDistanceFromStore = $data['maxDistanceFromStore'];

        // Initialize variables to 0 or empty before the null check

        // Initialize proximity data
        $averageDistanceTalentPoolApplicants = 0;
        $averageDistanceApplicantsAppointed = 0;

        // Fetch scores only if $type is not null
        if ($type !== null) {
            // Fetch proximity data from ApplicantProximityService
            $averageDistanceTalentPoolApplicants = $this->applicantProximityService->getAverageDistanceTalentPoolApplicants($type, $id, $startDate, $endDate, $maxDistanceFromStore);
            $averageDistanceApplicantsAppointed = $this->applicantProximityService->getAverageDistanceApplicantsAppointed($type, $id, $startDate, $endDate);
        }

        // Return the calculated metrics as a JSON response:
        return response()->json([
            'averageDistanceTalentPoolApplicants' => $averageDistanceTalentPoolApplicants,
            'averageDistanceApplicantsAppointed' => $averageDistanceApplicantsAppointed
        ]);
    }

    /**
     * Retrieve average score metrics for vacancies.
     *
     * This method calculates and returns key metrics related to candidate scores,
     * including the average score of the talent pool and successful placements.
     * The data is returned as a JSON response.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getAverageScoreMetrics(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Initialize variables to 0 or empty before the null check
        $averageScoreTalentPoolApplicants = 0;
        $averageScoreApplicantsAppointed = 0;

        // Fetch scores only if $type is not null
        if ($type !== null) {
            // Fetch score data from ApplicantDataService
            $averageScoreTalentPoolApplicants = $this->applicantDataService->getAverageScoreTalentPoolApplicants($type, $id, $startDate, $endDate);
            $averageScoreApplicantsAppointed = $this->applicantDataService->getAverageScoreApplicantsAppointed($type, $id, $startDate, $endDate);
        }

        // Return the calculated metrics as a JSON response
        return response()->json([
            'averageScoreTalentPoolApplicants' => $averageScoreTalentPoolApplicants,
            'averageScoreApplicantsAppointed' => $averageScoreApplicantsAppointed
        ]);
    }

    /**
     * Retrieve assessment score metrics.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getAssessmentScores(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Initialize applicants assessment scores
        $literacyStateID = State::where('code', 'literacy')->first()->id;
        $literacyQuestionsCount = ChatTemplate::where('state_id', $literacyStateID)->count();
        $averageLiteracyScoreTalentPoolApplicants = 0;

        $numeracyStateID = State::where('code', 'numeracy')->first()->id;
        $numeracyQuestionsCount = ChatTemplate::where('state_id', $numeracyStateID)->count();
        $averageNumeracyScoreTalentPoolApplicants = 0;

        $situationalStateID = State::where('code', 'situational')->first()->id;
        $situationalQuestionsCount = ChatTemplate::where('state_id', $situationalStateID)->count();
        $averageSituationalScoreTalentPoolApplicants = 0;

        // Fetch scores only if $type is not null
        if ($type !== null) {
            // Fetch applicants assessment scores from applicantDataService
            $averageLiteracyScoreTalentPoolApplicants = $this->applicantDataService->getAverageLiteracyScoreTalentPoolApplicants($type, $id, $startDate, $endDate);
            $averageNumeracyScoreTalentPoolApplicants = $this->applicantDataService->getAverageNumeracyScoreTalentPoolApplicants($type, $id, $startDate, $endDate);
            $averageSituationalScoreTalentPoolApplicants = $this->applicantDataService->getAverageSituationalScoreTalentPoolApplicants($type, $id, $startDate, $endDate);
        }

        // Return the calculated metrics as a JSON response
        return response()->json([
            'literacyQuestionsCount' => $literacyQuestionsCount,
            'averageLiteracyScoreTalentPoolApplicants' => $averageLiteracyScoreTalentPoolApplicants,
            'numeracyQuestionsCount' => $numeracyQuestionsCount,
            'averageNumeracyScoreTalentPoolApplicants' => $averageNumeracyScoreTalentPoolApplicants,
            'situationalQuestionsCount' => $situationalQuestionsCount,
            'averageSituationalScoreTalentPoolApplicants' => $averageSituationalScoreTalentPoolApplicants
        ]);
    }

    /**
     * Retrieve vacancies-related metrics.
     *
     * This method calculates and returns key metrics related to vacancies,
     * including the total number of created vacancies and the total number of filled vacancies.
     * The data is returned as a JSON response.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getVacanciesMetrics(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range (from the start of the year to the end of today)
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Initialize variables to 0 before the null check
        $totalVacancies = 0;
        $totalVacanciesFilled = 0;

        // Fetch scores only if $type is not null
        if ($type !== null) {
            // Fetch vacancy data from VacancyDataService
            $totalVacancies = $this->vacancyDataService->getTotalVacancies($type, $id, $startDate, $endDate);
            $totalVacanciesFilled = $this->vacancyDataService->getTotalVacanciesFilled($type, $id, $startDate, $endDate);
        }

        // Return the calculated metrics as a JSON response
        return response()->json([
            'totalVacancies' => $totalVacancies,
            'totalVacanciesFilled' => $totalVacanciesFilled
        ]);
    }

    /**
     * Retrieve interviews-related metrics.
     *
     * This method calculates and returns key metrics related to interviews,
     * including the total number of scheduled interviews and the total number of completed interviews.
     * The data is returned as a JSON response.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getInterviewsMetrics(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range (from the start of the year to the end of today)
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Initialize variables to 0 before the null check
        $totalInterviewsScheduled = 0;
        $totalInterviewsCompleted = 0;

        // Fetch scores only if $type is not null
        if ($type !== null) {
            // Fetch interview data from VacancyDataService
            $totalInterviewsScheduled = $this->vacancyDataService->getTotalInterviewsScheduled($type, $id, $startDate, $endDate);
            $totalInterviewsCompleted = $this->vacancyDataService->getTotalInterviewsCompleted($type, $id, $startDate, $endDate);
        }

        // Return the calculated metrics as a JSON response
        return response()->json([
            'totalInterviewsScheduled' => $totalInterviewsScheduled,
            'totalInterviewsCompleted' => $totalInterviewsCompleted
        ]);
    }

    /**
     * Retrieve applicants-related metrics.
     *
     * This method calculates and returns key metrics related to applicants,
     * including the total number of applicants appointed and the total number of applicants regretted.
     * The data is returned as a JSON response.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getApplicantsMetrics(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range (from the start of the year to the end of today)
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Initialize variables to 0 before the null check
        $totalInterviewsScheduled = 0;
        $totalApplicantsAppointed = 0;
        $totalApplicantsRegretted = 0;

        // Fetch scores only if $type is not null
        if ($type !== null) {
            // Fetch interview data from VacancyDataService
            $totalInterviewsScheduled = $this->vacancyDataService->getTotalInterviewsScheduled($type, $id, $startDate, $endDate);

            // Fetch applicant data from ApplicantDataService
            $totalApplicantsAppointed = $this->vacancyDataService->getTotalApplicantsAppointed($type, $id, $startDate, $endDate);
            $totalApplicantsRegretted = $this->vacancyDataService->getTotalApplicantsRegretted($type, $id, $startDate, $endDate);
        }

        // Return the calculated metrics as a JSON response
        return response()->json([
            'totalInterviewsScheduled' => $totalInterviewsScheduled,
            'totalApplicantsAppointed' => $totalApplicantsAppointed,
            'totalApplicantsRegretted' => $totalApplicantsRegretted
        ]);
    }

    /**
     * Retrieve talent pool-related metrics.
     *
     * This method calculates and returns key metrics related to the talent pool,
     * including the total number of talent pool applicants, applicants appointed,
     * and the monthly breakdown for both metrics.
     * The data is returned as a JSON response.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getTalentPoolMetrics(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range (from the start of the year to the end of today)
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Get the max proximity from store
        $maxDistanceFromStore = $data['maxDistanceFromStore'];

        // Initialize variables to 0 or empty arrays before the null check
        $talentPoolApplicants = 0;
        $talentPoolApplicantsByMonth = [];
        $applicantsAppointed = 0;
        $applicantsAppointedByMonth = [];

        // Fetch scores only if $type is not null
        if ($type !== null) {
            // Fetch talent pool data from applicantProximityService
            $talentPoolApplicants = $this->applicantProximityService->getTalentPoolApplicants($type, $id, $startDate, $endDate, $maxDistanceFromStore);
            $talentPoolApplicantsByMonth = $this->applicantProximityService->getTalentPoolApplicantsByMonth($type, $id, $startDate, $endDate, $maxDistanceFromStore);

            // Fetch applicants appointed data from vacancyDataService
            $applicantsAppointed = $this->vacancyDataService->getApplicantsAppointed($type, $id, $startDate, $endDate);
            $applicantsAppointedByMonth = $this->vacancyDataService->getApplicantsAppointedByMonth($type, $id, $startDate, $endDate);
        }

        // Return the calculated metrics as a JSON response
        return response()->json([
            'talentPoolApplicants' => $talentPoolApplicants,
            'talentPoolApplicantsByMonth' => $talentPoolApplicantsByMonth,
            'applicantsAppointed' => $applicantsAppointed,
            'applicantsAppointedByMonth' => $applicantsAppointedByMonth
        ]);
    }

    /**
     * Retrieve application channels-related metrics.
     *
     * This method calculates and returns metrics related to the distribution
     * of applications across various channels, such as website and WhatsApp.
     * The data is returned as a JSON response.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getApplicationChannelsMetrics(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Get the max proximity from store
        $maxDistanceFromStore = $data['maxDistanceFromStore'];

        // Initialize variables
        $totalWhatsAppApplicants = 0;
        $totalWebsiteApplicants = 0;

        // Fetch scores only if $type is not null
        if ($type !== null) {
            // Fetch talent pool data from applicantProximityService
            $talentPoolApplicants = $this->applicantProximityService->getTalentPoolApplicants($type, $id, $startDate, $endDate, $maxDistanceFromStore);

            // Fetch application channel data from applicantDataService
            $totalWhatsAppApplicants = $this->applicantDataService->getTotalWhatsAppApplicants($type, $id, $startDate, $endDate);
            $totalWebsiteApplicants = $this->applicantDataService->getTotalWebsiteApplicants($type, $id, $startDate, $endDate);
        }

        // Return the calculated metrics as a JSON response
        return response()->json([
            'talentPoolApplicants' => $talentPoolApplicants,
            'totalWhatsAppApplicants' => $totalWhatsAppApplicants,
            'totalWebsiteApplicants' => $totalWebsiteApplicants
        ]);
    }

    /**
     * Retrieve application completion-related metrics.
     *
     * This method calculates and returns metrics related to the completion rates
     * of applications within the specified date range.
     * The data is returned as a JSON response.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getApplicationCompletionMetrics(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Initialize variables
        $completionRate = 0;
        $totalApplicants = 0;
        $totalCompletedApplicants = 0;
        $completionRate = 0;
        $dropOffState = 'None';
        $dropOffChat = '';

        // Fetch scores only if $type is not null
        if ($type !== null) {
            // Fetch the completion rate and drop of state from applicantDataService
            $totalApplicants = $this->applicantDataService->getTotalApplicants($type, $id, $startDate, $endDate);
            $totalCompletedApplicants = $this->applicantDataService->getTotalCompletedApplicants($type, $id, $startDate, $endDate);
            $completionRate = ($totalApplicants > 0) ? round($totalCompletedApplicants / $totalApplicants * 100) : 0;
            $dropOffState = $this->applicantDataService->getdropOffState($type, $id, $startDate, $endDate);
            if (!empty($dropOffState)) {
                // Find the State where name matches $dropOffState
                $state = State::where('name', $dropOffState)->first();

                // If the state exists, get the corresponding ChatTemplate
                if ($state) {
                    $dropOffChat = ChatTemplate::where('state_id', $state->id)->first();
                }
            }
        }

        // Return the calculated metrics as a JSON response
        return response()->json([
            'completionRate' => $completionRate,
            'dropOffState' => $dropOffState,
            'dropOffChat' => $dropOffChat
        ]);
    }

    /**
     * Retrieve stores-related metrics.
     *
     * This method calculates and returns metrics related to stores, such as
     * the number of stores with vacancies or talent pool applicants within proximity.
     * The data is returned as a JSON response.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getStoresMetrics(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Initialize variables
        $totalStoresUsingSolution = 0;
        $totalStores = 0;
        $totalReEmployedApplicants = 0;
        $totalAppointedApplicants = 0;

        // Fetch scores only if $type is not null
        if ($type !== null) {
            $totalStoresUsingSolution = $this->vacancyDataService->getTotalStoresUsingSolution($type, $id, $startDate, $endDate);
            $totalStores = $this->vacancyDataService->getTotalStores($type, $id, $startDate, $endDate);
            $totalReEmployedApplicants = $this->applicantDataService->getTotalReEmployedApplicants($type, $id, $startDate, $endDate);
            $totalAppointedApplicants = $this->applicantDataService->getTotalAppointedApplicants($type, $id, $startDate, $endDate);
        }

        // Return the calculated metrics as a JSON response
        return response()->json([
            'totalStoresUsingSolution' => $totalStoresUsingSolution,
            'totalStores' => $totalStores,
            'totalReEmployedApplicants' => $totalReEmployedApplicants,
            'totalAppointedApplicants' => $totalAppointedApplicants
        ]);
    }

    /**
     * Retrieve demographic-related metrics.
     *
     * This method calculates and returns metrics related to demographic distributions,
     * such as total applicants per demographic category.
     * The data is returned as a JSON response.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getDemographicMetrics(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Get the max proximity from store
        $maxDistanceFromStore = $data['maxDistanceFromStore'];

        // Initialize variables
        $talentPoolApplicantsDemographic = [];
        $interviewedApplicantsDemographic = [];
        $appointedApplicantsDemographic = [];

        // Fetch data only if $type is not null
        if ($type !== null) {
            // Fetch applicant demographic data from applicantDataService
            $talentPoolApplicantsDemographic = $this->applicantDataService->getTalentPoolApplicantsDemographic($type, $id, $startDate, $endDate, $maxDistanceFromStore);
            $interviewedApplicantsDemographic = $this->applicantDataService->getInterviewedApplicantsDemographic($type, $id, $startDate, $endDate);
            $appointedApplicantsDemographic = $this->applicantDataService->getAppointedApplicantsDemographic($type, $id, $startDate, $endDate);
        }

        // Return the calculated metrics as a JSON response
        return response()->json([
            'talentPoolApplicantsDemographic' => $talentPoolApplicantsDemographic,
            'interviewedApplicantsDemographic' => $interviewedApplicantsDemographic,
            'appointedApplicantsDemographic' => $appointedApplicantsDemographic
        ]);
    }

    /**
     * Retrieve gender-related metrics.
     *
     * This method calculates and returns metrics related to gender distributions,
     * such as total male and female applicants.
     * The data is returned as a JSON response.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getGenderMetrics(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Get the max proximity from store
        $maxDistanceFromStore = $data['maxDistanceFromStore'];

        // Initialize variables
        $talentPoolApplicantsGender = [];
        $interviewedApplicantsGender = [];
        $appointedApplicantsGender = [];

        // Fetch data only if $type is not null
        if ($type !== null) {
            // Fetch applicant gender data from applicantDataService
            $talentPoolApplicantsGender = $this->applicantDataService->getTalentPoolApplicantsGender($type, $id, $startDate, $endDate, $maxDistanceFromStore);
            $interviewedApplicantsGender = $this->applicantDataService->getInterviewedApplicantsGender($type, $id, $startDate, $endDate);
            $appointedApplicantsGender = $this->applicantDataService->getAppointedApplicantsGender($type, $id, $startDate, $endDate);
        }

        // Return the calculated metrics as a JSON response
        return response()->json([
            'talentPoolApplicantsGender' => $talentPoolApplicantsGender,
            'interviewedApplicantsGender' => $interviewedApplicantsGender,
            'appointedApplicantsGender' => $appointedApplicantsGender
        ]);
    }

    /**
     * Retrieve province-related metrics.
     *
     * This method calculates and returns metrics related to applicant distributions across provinces,
     * such as total applicants by province.
     * The data is returned as a JSON response.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the metrics.
     */
    public function getProvinceMetrics(Request $request)
    {
        // Retrieve common metrics data
        $data = $this->getCommonMetricsData();

        // Define the date range
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        // Determine the $type and $id based on the user's role
        $type = $data['type'];
        $id = $data['id'];

        // Initialize variables
        $talentPoolApplicantsProvince = [];

        // Fetch data only if $type is not null
        if ($type !== null) {
            $talentPoolApplicantsProvince = $this->applicantDataService->getTalentPoolApplicantsProvince($type, $id, $startDate, $endDate);
        }

        // Return the calculated metrics as a JSON response
        return response()->json([
            'talentPoolApplicantsProvince' => $talentPoolApplicantsProvince
        ]);
    }
}
