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

    /**
     * Calculate the nationwide average time to hire.
     *
     * @return float|null
     */
    public function getNationwideAverageTimeToHire()
    {
        return DB::table('vacancies')
            ->join('vacancy_fills', 'vacancies.id', '=', 'vacancy_fills.vacancy_id')
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, vacancy_fills.created_at))) as avg_time_to_hire'))
            ->value('avg_time_to_hire');
    }

    /**
     * Calculate the time to hire for all vacancies in a specific region.
     *
     * @param int $regionId
     * @return float|null
     */
    public function getRegionWideAverageTimeToHire(int $regionId)
    {
        return DB::table('vacancies')
            ->join('vacancy_fills', 'vacancies.id', '=', 'vacancy_fills.vacancy_id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->where('stores.region_id', $regionId)
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, vacancy_fills.created_at))) as avg_time_to_hire'))
            ->value('avg_time_to_hire');
    }

    /**
     * Calculate the time to hire for a specific division.
     *
     * @param int $divisionId
     * @return float|null
     */
    public function getDivisionWideAverageTimeToHire(int $divisionId)
    {
        return DB::table('vacancies')
            ->join('vacancy_fills', 'vacancies.id', '=', 'vacancy_fills.vacancy_id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->where('stores.division_id', $divisionId)
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, vacancy_fills.created_at))) as avg_time_to_hire'))
            ->value('avg_time_to_hire');
    }

    /**
     * Calculate the time to hire for a specific store.
     *
     * @param int $storeId
     * @return float|null
     */
    public function getStoreAverageTimeToHire(int $storeId)
    {
        return DB::table('vacancies')
            ->join('vacancy_fills', 'vacancies.id', '=', 'vacancy_fills.vacancy_id')
            ->where('vacancies.store_id', $storeId)
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, vacancy_fills.created_at))) as avg_time_to_hire'))
            ->value('avg_time_to_hire');
    }

    /**
     * Calculate the average time to hire for a specific time range (e.g., current month).
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $storeId (optional, if you want to filter by store as well)
     * @return float
     */
    public function getTimeFilteredAverageToHire(Carbon $startDate, Carbon $endDate, int $storeId = null)
    {
        $query = DB::table('vacancies')
            ->join('vacancy_fills', 'vacancies.id', '=', 'vacancy_fills.vacancy_id')
            ->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]);

        if ($storeId) {
            $query->where('vacancies.store_id', $storeId);
        }

        return $query->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, vacancy_fills.created_at))) as avg_time_to_hire'))
            ->value('avg_time_to_hire');
    }

    /**
     * Calculate the vacancy fill rate for all vacancies system-wide (nationwide).
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getNationwideVacancyFillRate(Carbon $startDate = null, Carbon $endDate = null): float
    {
        $query = DB::table('vacancies')
            ->select(
                DB::raw('SUM(vacancies.open_positions) as total_open_positions'),
                DB::raw('SUM(vacancies.filled_positions) as total_filled_positions')
            );

        if ($startDate && $endDate) {
            $query->whereBetween('vacancies.created_at', [$startDate, $endDate]);
        }

        $result = $query->first();

        if ($result && $result->total_open_positions > 0) {
            $fillRate = ($result->total_filled_positions / $result->total_open_positions) * 100;
            return (float) round($fillRate, 2);
        }

        return 0.0;
    }

    /**
     * Calculate the vacancy fill rate for a specific division.
     *
     * @param int $divisionId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getDivisionVacancyFillRate(int $divisionId, Carbon $startDate = null, Carbon $endDate = null): float
    {
        $query = DB::table('vacancies')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->where('stores.division_id', $divisionId)
            ->select(
                DB::raw('SUM(vacancies.open_positions) as total_open_positions'),
                DB::raw('SUM(vacancies.filled_positions) as total_filled_positions')
            );

        if ($startDate && $endDate) {
            $query->whereBetween('vacancies.created_at', [$startDate, $endDate]);
        }

        $result = $query->first();

        if ($result && $result->total_open_positions > 0) {
            $fillRate = ($result->total_filled_positions / $result->total_open_positions) * 100;
            return (float) round($fillRate, 2);
        }

        return 0.0;
    }

    /**
     * Calculate the vacancy fill rate for a specific region.
     *
     * @param int $regionId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getRegionVacancyFillRate(int $regionId, Carbon $startDate = null, Carbon $endDate = null): float
    {
        $query = DB::table('vacancies')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->where('stores.region_id', $regionId)
            ->select(
                DB::raw('SUM(vacancies.open_positions) as total_open_positions'),
                DB::raw('SUM(vacancies.filled_positions) as total_filled_positions')
            );

        if ($startDate && $endDate) {
            $query->whereBetween('vacancies.created_at', [$startDate, $endDate]);
        }

        $result = $query->first();

        if ($result && $result->total_open_positions > 0) {
            $fillRate = ($result->total_filled_positions / $result->total_open_positions) * 100;
            return (float) round($fillRate, 2);
        }

        return 0.0;
    }

    /**
     * Calculate the vacancy fill rate for a specific store.
     *
     * @param int $storeId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getStoreVacancyFillRate(int $storeId, Carbon $startDate = null, Carbon $endDate = null): float
    {
        $query = DB::table('vacancies')
            ->where('vacancies.store_id', $storeId)
            ->select(
                DB::raw('SUM(vacancies.open_positions) as total_open_positions'),
                DB::raw('SUM(vacancies.filled_positions) as total_filled_positions')
            );

        if ($startDate && $endDate) {
            $query->whereBetween('vacancies.created_at', [$startDate, $endDate]);
        }

        $result = $query->first();

        if ($result && $result->total_open_positions > 0) {
            $fillRate = ($result->total_filled_positions / $result->total_open_positions) * 100;
            return (float) round($fillRate, 2);
        }

        return 0.0;
    }
}
