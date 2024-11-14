<?php

namespace App\Services\DataService\Reports;

use Carbon\Carbon;
use App\Models\Type;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Collection;

class VacanciesReportDataService
{
    /**
     * Fetch the Total Vacancies based on type and date range using the Vacancy model and Eloquent relationships.
     *
     * @param string $type The type of view (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param string $startDate The start date for filtering vacancies.
     * @param string $endDate The end date for filtering vacancies.
     * @return int The total count of vacancies.
     */
    public function getTotalVacancies(string $type, ?int $id, string $startDate, string $endDate)
    {
        // Start building the query using the Vacancy model and filter by date range
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate]);

        // Prioritize filtering by store, followed by division, then region using Eloquent relationships
        if ($type === 'store') {
            $vacancies->where('store_id', $id);
        } elseif ($type === 'division') {
            $vacancies->whereHas('store', function ($query) use ($id) {
                $query->where('division_id', $id);
            });
        } elseif ($type === 'region') {
            $vacancies->whereHas('store', function ($query) use ($id) {
                $query->where('region_id', $id);
            });
        }

        // Return the total count of vacancies
        return $vacancies->count();
    }

    /**
     * Fetch the Total Vacancies Filled based on type and date range using the Vacancy model and Eloquent relationships.
     *
     * @param string $type The type of view (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param string $startDate The start date for filtering vacancies.
     * @param string $endDate The end date for filtering vacancies.
     * @return int The total count of filled vacancies.
     */
    public function getTotalVacanciesFilled(string $type, ?int $id, string $startDate, string $endDate)
    {
        // Start building the query using the Vacancy model, filter for filled vacancies (open_positions = 0), and date range
        $vacancies = Vacancy::where('open_positions', 0)
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Prioritize filtering by store, followed by division, then region using Eloquent relationships
        if ($type === 'store') {
            $vacancies->where('store_id', $id);
        } elseif ($type === 'division') {
            $vacancies->whereHas('store', function ($query) use ($id) {
                $query->where('division_id', $id);
            });
        } elseif ($type === 'region') {
            $vacancies->whereHas('store', function ($query) use ($id) {
                $query->where('region_id', $id);
            });
        }

        // Return the total count of filled vacancies
        return $vacancies->count();
    }

    /**
     * Get the total number of vacancies created by month within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @return array An array of vacancy counts by month.
     */
    public function getTotalVacanciesByMonth(string $type, ?int $id, $startDate, $endDate): array
    {
        // Initialize an array to hold the results, with months set to 0 from startDate to endDate
        $vacanciesByMonth = [];
        $currentDate = $startDate->copy();

        // Loop to populate only the months between startDate and endDate
        while ($currentDate->lte($endDate)) {
            $monthName = $currentDate->format('M');
            $vacanciesByMonth[$monthName] = 0;
            $currentDate->addMonth();
        }

        // Start building the query using the Vacancy model and filter by date range
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate]);

        // Filter by store, division, or region based on the type parameter
        if ($type === 'store') {
            $vacancies->where('store_id', $id);
        } elseif ($type === 'division') {
            $vacancies->whereHas('store', function ($query) use ($id) {
                $query->where('division_id', $id);
            });
        } elseif ($type === 'region') {
            $vacancies->whereHas('store', function ($query) use ($id) {
                $query->where('region_id', $id);
            });
        }

        // Retrieve vacancies and group them by the month of their creation date
        foreach ($vacancies->get() as $vacancy) {
            $month = $vacancy->created_at->format('M');
            $vacanciesByMonth[$month]++;
        }

        return $vacanciesByMonth;
    }

    /**
     * Get the total number of filled vacancies by month within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @return array An array of filled vacancy counts by month.
     */
    public function getTotalVacanciesFilledByMonth(string $type, ?int $id, $startDate, $endDate): array
    {
        // Initialize an array to hold the results, with months set to 0 from startDate to endDate
        $filledVacanciesByMonth = [];
        $currentDate = $startDate->copy();

        // Loop to populate only the months between startDate and endDate
        while ($currentDate->lte($endDate)) {
            $monthName = $currentDate->format('M');
            $filledVacanciesByMonth[$monthName] = 0;
            $currentDate->addMonth();
        }

        // Start building the query using the Vacancy model, filter for filled vacancies (open_positions = 0) and date range using updated_at
        $vacancies = Vacancy::where('open_positions', 0)
            ->whereBetween('updated_at', [$startDate, $endDate]);

        // Apply filters based on type (store, division, or region)
        if ($type === 'store') {
            $vacancies->where('store_id', $id);
        } elseif ($type === 'division') {
            $vacancies->whereHas('store', function ($query) use ($id) {
                $query->where('division_id', $id);
            });
        } elseif ($type === 'region') {
            $vacancies->whereHas('store', function ($query) use ($id) {
                $query->where('region_id', $id);
            });
        }

        // Retrieve vacancies and group them by the month of their updated_at date
        foreach ($vacancies->get() as $vacancy) {
            $month = $vacancy->updated_at->format('M');
            $filledVacanciesByMonth[$month]++;
        }

        return $filledVacanciesByMonth;
    }

    /**
     * Get the total number of vacancies by type and month within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @return array An array of vacancy counts by type and month.
     */
    public function getTotalVacanciesTypeByMonth(string $type, ?int $id, $startDate, $endDate): array
    {
        // Initialize an array to hold the results, with months set to 0 for each type from startDate to endDate
        $vacanciesTypeByMonth = [];
        $currentDate = $startDate->copy();

        // Retrieve all available vacancy types
        $vacancyTypes = Type::all();

        // Loop to populate the array for each month and type
        while ($currentDate->lte($endDate)) {
            $monthName = $currentDate->format('M');
            foreach ($vacancyTypes as $vacancyType) {
                // Set initial count for each month and type to 0
                $vacanciesTypeByMonth[$vacancyType->name][$monthName] = 0;
            }
            $currentDate->addMonth();
        }

        // Start building the query using the Vacancy model and filter by date range
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate]);

        // Apply filters based on the type (store, division, or region)
        if ($type === 'store') {
            $vacancies->where('store_id', $id);
        } elseif ($type === 'division') {
            $vacancies->whereHas('store', function ($query) use ($id) {
                $query->where('division_id', $id);
            });
        } elseif ($type === 'region') {
            $vacancies->whereHas('store', function ($query) use ($id) {
                $query->where('region_id', $id);
            });
        }

        // Retrieve vacancies and group them by the month of their created_at date and type
        foreach ($vacancies->get() as $vacancy) {
            $month = $vacancy->created_at->format('M');
            $vacancyTypeName = $vacancy->type->name; // Get type name directly
            $vacanciesTypeByMonth[$vacancyTypeName][$month]++;
        }

        return $vacanciesTypeByMonth;
    }

    /**
     * Get the total number of vacancies by type within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @return array An array with vacancy counts by type.
     */
    public function getTotalVacanciesByType(string $type, ?int $id, $startDate, $endDate): array
    {
        // Initialize an array to hold the results, with each type initialized to 0
        $vacanciesByType = [];

        // Retrieve all available vacancy types
        $vacancyTypes = Type::all();

        // Initialize each type count to 0
        foreach ($vacancyTypes as $vacancyType) {
            $vacanciesByType[$vacancyType->name] = 0;
        }

        // Start building the query using the Vacancy model and filter by date range
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate]);

        // Apply filters based on the type (store, division, or region)
        if ($type === 'store') {
            $vacancies->where('store_id', $id);
        } elseif ($type === 'division') {
            $vacancies->whereHas('store', function ($query) use ($id) {
                $query->where('division_id', $id);
            });
        } elseif ($type === 'region') {
            $vacancies->whereHas('store', function ($query) use ($id) {
                $query->where('region_id', $id);
            });
        }

        // Retrieve vacancies and group them by type
        foreach ($vacancies->get() as $vacancy) {
            $vacancyTypeName = $vacancy->type->name; // Get type name directly
            $vacanciesByType[$vacancyTypeName]++;
        }

        return $vacanciesByType;
    }

    /**
     * Summary of getFilteredVacancies
     * @param mixed $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFilteredVacancies($filters): Collection
    {
        $query = Vacancy::query();
        $this->applyFilters($query, $filters);
        return $query->get();
    }

    /**
     * Apply the given filters to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    public function applyFilters($query, array $filters): void
    {
        if (!empty($filters['position_id'])) {
            $query->where('position_id', $filters['position_id']);
        }

        if (!empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['type_id'])) {
            $query->where('type_id', $filters['type_id']);
        }

        if (!empty($filters['filled_positions'])) {
            $query->where('filled_positions', $filters['filled_positions']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($filters['start_date']),
                Carbon::parse($filters['end_date']),
            ]);
        }
    }

    /**
     * Summary of prepareChartData
     * @param \Illuminate\Database\Eloquent\Collection $vacancies
     * @param mixed $startDate
     * @param mixed $endDate
     * @return array
     */
    public function prepareChartData(Collection $vacancies, $startDate, $endDate)
    {
        $monthsForVacancyTypes = $this->getVacancyTypesRange($startDate, $endDate);

        $monthsForVacanciesOverTime = $this->getVacanciesOverTimeRange($startDate, $endDate);

         // Group vacancies by year and month
         $vacancyTypesByYearMonth = $this->getVacancyTypesByYearMonth($vacancies);

        // Group vacancies by year and month
        $vacanciesOverTimeYearMonth = $this->getVacanciesOverTimeYearMonth($vacancies);

        // Totals for each type of vacancy
        $totalFullTime = $vacancies->where('type_id', 1)->count();
        $totalPartTime = $vacancies->where('type_id', 2)->count();
        $totalFixedTerm = $vacancies->where('type_id', 3)->count();
        $totalPeakSeason = $vacancies->where('type_id', 4)->count();
        $totalVacancies = $vacancies->where('open_positions', '!=', 0)->count();
        $totalFilledVacancies = $vacancies->where('open_positions', 0)->count();

        // Merge actual data with zero-filled months
        $vacancyTypesByMonth = array_replace($monthsForVacancyTypes, $vacancyTypesByYearMonth);
        $vacanciesOverTime = array_replace($monthsForVacanciesOverTime, $vacanciesOverTimeYearMonth);

        return [
            'vacancyTypesByMonth' => $vacancyTypesByMonth,
            'vacanciesOverTime' => $vacanciesOverTime,
            'totals' => [
                'totalFullTime' => $totalFullTime,
                'totalPartTime' => $totalPartTime,
                'totalFixedTerm' => $totalFixedTerm,
                'totalPeakSeason' => $totalPeakSeason,
                'totalVacancies' => $totalVacancies,
                'totalFilledVacancies' => $totalFilledVacancies,
            ]
        ];
    }

    /**
     * Summary of getVacancyTypesRange
     * @param mixed $startDate
     * @param mixed $endDate
     * @return array{FixedTerm: int, FullTime: int, PartTime: int, PeakSeason: int, Total: int[]}
     */
    private function getVacancyTypesRange($startDate, $endDate)
    {
        $monthsForVacancyTypes = [];
        $typeCurrent = Carbon::parse($startDate)->startOfMonth();
        $typeEnd = Carbon::parse($endDate)->startOfMonth();

        while ($typeCurrent <= $typeEnd) {
            $monthsForVacancyTypes[$typeCurrent->format('Y-F')] = [
                'FullTime' => 0,
                'PartTime' => 0,
                'FixedTerm' => 0,
                'PeakSeason' => 0,
                'Total' => 0
            ];
            $typeCurrent->addMonth();
        }

        return $monthsForVacancyTypes;
    }

    /**
     * Summary of getVacanciesOverTimeRange
     * @param mixed $startDate
     * @param mixed $endDate
     * @return array{filled: int, total: int[]}
     */
    private function getVacanciesOverTimeRange($startDate, $endDate)
    {
        $monthsForVacanciesOverTime = [];
        $overTimeCurrent = Carbon::parse($startDate)->startOfMonth();
        $overTimeEnd = Carbon::parse($endDate)->startOfMonth();
        while ($overTimeCurrent <= $overTimeEnd) {
            $monthsForVacanciesOverTime[$overTimeCurrent->format('Y-F')] = [
                'total' => 0,
                'filled' => 0
            ];
            $overTimeCurrent->addMonth();
        }

        return $monthsForVacanciesOverTime;
    }

    /**
     * Summary of getVacancyTypesByYearMonth
     * @param \Illuminate\Database\Eloquent\Collection $vacancies
     * @return array
     */
    private function getVacancyTypesByYearMonth(Collection $vacancies)
    {
        return $vacancies->groupBy(function ($vacancy) {
            return $vacancy->created_at->format('Y-F');
        })->map(function ($group) {
            return [
                'FullTime' => $group->where('type_id', 1)->count(),
                'PartTime' => $group->where('type_id', 2)->count(),
                'FixedTerm' => $group->where('type_id', 3)->count(),
                'PeakSeason' => $group->where('type_id', 4)->count(),
                'Total' => $group->count(),
            ];
        })->toArray();
    }

    /**
     * Summary of getVacanciesOverTimeYearMonth
     * @param \Illuminate\Database\Eloquent\Collection $vacancies
     * @return array
     */
    private function getVacanciesOverTimeYearMonth(Collection $vacancies)
    {
        return $vacancies->groupBy(function ($vacancy) {
            return $vacancy->created_at->format('Y-F');
        })->map(function ($group) {
            return [
                'total' => $group->where('open_positions', '!=', 0)->count(),
                'filled' => $group->where('open_positions', 0)->count()
            ];
        })->toArray();
    }
}
