<?php

namespace App\Services\DataService;

use App\Models\Shortlist;
use App\Models\Vacancy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VacancyDataService
{
    /**
     * Calculate the average time to shortlist for all vacancies (nationwide).
     *
     * @return float
     */
    public function getNationwideAverageTimeToShortlist()
    {
        return DB::table('vacancies')
            ->join('shortlists', 'vacancies.id', '=', 'shortlists.vacancy_id')
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, shortlists.created_at))) as avg_time_to_shortlist'))
            ->value('avg_time_to_shortlist');
    }

    /**
     * Calculate the average time to shortlist for all vacancies in a specific region.
     *
     * @param int $regionId
     * @return float
     */
    public function getRegionWideAverageTimeToShortlist(int $regionId)
    {
        return DB::table('vacancies')
            ->join('shortlists', 'vacancies.id', '=', 'shortlists.vacancy_id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->where('stores.region_id', $regionId)
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, shortlists.created_at))) as avg_time_to_shortlist'))
            ->value('avg_time_to_shortlist');
    }

    /**
     * Calculate the average time to shortlist for all vacancies in the authenticated user's division.
     * Applicable for DDP and DTDP users.
     *
     * @param int $divisionId
     * @return float
     */
    public function getDivisionWideAverageTimeToShortlist(int $divisionId)
    {
        return DB::table('vacancies')
            ->join('shortlists', 'vacancies.id', '=', 'shortlists.vacancy_id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->where('stores.division_id', $divisionId)
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, shortlists.created_at))) as avg_time_to_shortlist'))
            ->value('avg_time_to_shortlist');
    }

    /**
     * Calculate the average time to shortlist for a specific store.
     *
     * @param int $storeId
     * @return float
     */
    public function getStoreAverageTimeToShortlist(int $storeId)
    {
        return DB::table('vacancies')
            ->join('shortlists', 'vacancies.id', '=', 'shortlists.vacancy_id')
            ->where('vacancies.store_id', $storeId)
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, shortlists.created_at))) as avg_time_to_shortlist'))
            ->value('avg_time_to_shortlist');
    }

    /**
     * Calculate the average time to shortlist for a specific time range (e.g., current month).
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $storeId (optional, if you want to filter by store as well)
     * @return float
     */
    public function getTimeFilteredAverageToShortlist(Carbon $startDate, Carbon $endDate, int $storeId = null)
    {
        $query = DB::table('vacancies')
            ->join('shortlists', 'vacancies.id', '=', 'shortlists.vacancy_id')
            ->whereBetween('shortlists.created_at', [$startDate, $endDate]);

        if ($storeId) {
            $query->where('vacancies.store_id', $storeId);
        }

        return $query->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, vacancies.created_at, shortlists.created_at)) as avg_time_to_shortlist'))
            ->value('avg_time_to_shortlist');
    }
}
