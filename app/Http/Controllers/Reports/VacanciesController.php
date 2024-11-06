<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Store;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\Status;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\DataService\Reports\VacanciesDataService;
use App\Exports\VacancyTypesExport;
use App\Exports\VacanciesOverTimeExport;
use Maatwebsite\Excel\Facades\Excel;

class VacanciesController extends Controller
{
    protected $vacanciesDataService;

    /**
     * Summary of __construct
     * @param \App\Services\DataService\Reports\VacanciesDataService $vacanciesDataService
     */
    public function __construct(VacanciesDataService $vacanciesDataService)
    {
        $this->vacanciesDataService = $vacanciesDataService;
    }

    /**
     * Summary of index
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        if (view()->exists('admin/reports/vacancies')) {
            $vacancies = Vacancy::with([
                'user',
                'position',
                'store',
                'type',
                'status',
            ])
            ->get();

            $users = User::whereHas('vacancies')->get();
            $stores = Store::whereHas('vacancies')->get();
            $positions = Position::whereHas('vacancies')->get();
            $types = Type::distinct('type_id')->get();
            $statuses = Status::distinct('status_id')->get();

            // Default date range: from the beginning of the year to today
            $defaultStartDate = Carbon::now()->startOfYear()->toDateString();
            $defaultEndDate = Carbon::now()->toDateString();

            return view('admin/reports/vacancies', compact('users', 'positions', 'stores', 'types', 'statuses', 'defaultStartDate', 'defaultEndDate'));
        }

        // If the view 'admin/reports/vacancies' does not exist, return a 404 error page
         return view('404');
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
            'start_date' => $request->start_date ?? Carbon::now()->startOfYear()->toDateString(),
            'end_date' => $request->end_date ?? Carbon::now()->toDateString(),
        ];

        $vacancies = $this->vacanciesDataService->getFilteredVacancies($filters);
        $chartData = $this->vacanciesDataService->prepareChartData($vacancies);

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully!',
            'chartData' => $chartData,
        ]);
    }

    public function exportVacancyTypes(Request $request)
    {
        $filters = $this->getFiltersFromRequest($request);
        return Excel::download(new VacancyTypesExport($filters), 'vacancy_types.xlsx');
    }

    public function exportVacanciesOverTime(Request $request)
    {
        $filters = $this->getFiltersFromRequest($request);
        return Excel::download(new VacanciesOverTimeExport($filters), 'vacancies_over_time.xlsx');
    }

    private function getFiltersFromRequest(Request $request)
    {
        $dateRange = explode(' to ', $request->get('date_range', ''));
        return [
            'position_id' => $request->position_id,
            'store_id' => $request->store_id,
            'user_id' => $request->user_id,
            'type_id' => $request->type_id,
            'start_date' => $dateRange[0] ?? null,
            'end_date' => $dateRange[1] ?? null,
        ];
    }
}
