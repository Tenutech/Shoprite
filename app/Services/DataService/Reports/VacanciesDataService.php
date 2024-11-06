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
     * @return array
     */
    public function prepareChartData(Collection $vacancies): array
    {
        $monthsOfYear = $this->getMonthsOfYear();

        // Group vacancies by month and type
        $vacancyTypesByMonth = $this->getVacanciesByMonth($vacancies);

        // Get total vacancies and filled vacancies over time
        $vacanciesOverTime = $this->getVacanciesOverTime($vacancies);

        // Totals for each type of vacancy
        $totalFullTime = $vacancies->where('type_id', 1)->count();
        $totalPartTime = $vacancies->where('type_id', 2)->count();
        $totalFixedTerm = $vacancies->where('type_id', 3)->count();
        $totalPeakSeason = $vacancies->where('type_id', 4)->count();
        $totalVacancies = $vacancies->where('open_positions', '!=', 0)->count();
        $totalFilledVacancies = $vacancies->where('open_positions', 0)->count();

        return [
            'vacancyTypesByMonth' => array_replace(array_fill_keys($monthsOfYear, ['FullTime' => 0, 'PartTime' => 0, 'FixedTerm' => 0, 'PeakSeason' => 0]), $vacancyTypesByMonth->toArray()),
            'vacanciesOverTime' => array_replace(array_fill_keys($monthsOfYear, 0), $vacanciesOverTime),
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
     * Summary of getMonthsOfYear
     * @return array
     */
    private function getMonthsOfYear(): array
    {
        return [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
    }

    /**
     * Summary of getVacanciesByMonth
     * @param \Illuminate\Database\Eloquent\Collection $vacancies
     * @return Collection|\Illuminate\Support\Collection
     */
    private function getVacanciesByMonth(Collection $vacancies)
    {
        return $vacancies->groupBy(function ($vacancy) {
            return $vacancy->created_at->format('F');
        })->map(function ($group) {
            return [
                'FullTime' => $group->where('type_id', 1)->count(),
                'PartTime' => $group->where('type_id', 2)->count(),
                'FixedTerm' => $group->where('type_id', 3)->count(),
                'PeakSeason' => $group->where('type_id', 4)->count(),
            ];
        });
    }

    /**
     * Summary of getVacanciesOverTime
     * @param \Illuminate\Database\Eloquent\Collection $vacancies
     * @return array[]
     */
    private function getVacanciesOverTime(Collection $vacancies)
    {
        return [
            'total' => $vacancies->where('open_positions', '!=', 0)
                ->countBy(fn($vacancy) => $vacancy->created_at->format('F'))
                ->toArray(),
            'filled' => $vacancies->where('open_positions', 0)
                ->countBy(fn($vacancy) => $vacancy->updated_at->format('F'))
                ->toArray(),
        ];
    }
}
