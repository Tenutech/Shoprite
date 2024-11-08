<?php

namespace App\Services\DataService\Reports;

use Carbon\Carbon;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Collection;

class VacanciesDataService
{
    /**
     * Summary of getFilteredVacancies
     * @param mixed $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFilteredVacancies($filters): Collection
    {
        return Vacancy::query()
            ->when($filters['position_id'], fn($query) => $query->where('position_id', $filters['position_id']))
            ->when($filters['store_id'], fn($query) => $query->where('store_id', $filters['store_id']))
            ->when($filters['user_id'], fn($query) => $query->where('user_id', $filters['user_id']))
            ->when($filters['type_id'], fn($query) => $query->where('type_id', $filters['type_id']))
            ->when($filters['filled_positions'], fn($query) => $query->where('filled_positions', $filters['filled_positions']))
            ->when($filters['start_date'] && $filters['end_date'], function ($query) use ($filters) {
                $query->whereBetween('created_at', [
                    Carbon::parse($filters['start_date']),
                    Carbon::parse($filters['end_date']),
                ]);
            })
            ->get();
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
