<?php

namespace App\Services\DataService;

use App\Models\Applicant;
use App\Models\ApplicantMonthlyData;
use App\Models\ApplicantTotalData;
use App\Models\Province;
use App\Models\State;
use App\Models\Town;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApplicantDataService
{
    /**
     * Retrieve the total number of applicants per month.
     *
     * This method calculates the total number of applicants per month, considering data from
     * both the previous year and the current year up to the specified current month.
     *
     * @param \DateTimeInterface|string $startDate The start date of the date range.
     * @param \DateTimeInterface|string $endDate   The end date of the date range.
     *
     * @return array An associative array containing the total number of applicants per month.
     *               The keys represent the names of the months, and the values represent the total
     *               number of applicants for each month.
     */
    public function getApplicationsPerMonth($startDate, $endDate)
    {
        return $this->getMonthlyDataForTimeFrame('Application', $startDate, $endDate)
                    ->pluck('count')
                    ->toArray();
    }

    /**
     * Get applicant total data for a specified date range.
     *
     * This method retrieves applicant total data records from the database
     * where the 'year' column falls within the specified date range.
     *
     * @param \DateTimeInterface|string $startDate The start date of the date range.
     * @param \DateTimeInterface|string $endDate   The end date of the date range.
     *
     * @return \Illuminate\Database\Eloquent\Collection|\App\Models\ApplicantTotalData[]
     *     A collection of applicant total data records.
     */
    public function getApplicantTotalDataForDateRange($startDate, $endDate)
    {
        // Convert string dates to DateTime objects if necessary
        $startDate = $startDate instanceof \DateTimeInterface ? $startDate : new \DateTime($startDate);
        $endDate = $endDate instanceof \DateTimeInterface ? $endDate : new \DateTime($endDate);

        return ApplicantTotalData::whereBetween('year', [$startDate->format('Y'), $endDate->format('Y')])
            ->get();
    }

    /**
     * Get the number of applicants per province for the current year.
     *
     * This method retrieves the total number of applicants per province for the current year
     * from the monthly data associated with the given current year data.
     *
     * @param \DateTimeInterface|string $startDate The start date of the time frame.
     * @param \DateTimeInterface|string $endDate The end date of the time frame.
     *
     * @return array
     *     An array containing the number of applicants per province in the format
     *     ['x' => 'Province Name', 'y' => Total Applicants].
     */
    public function getApplicantsPerProvince($startDate, $endDate)
    {
        return $this->getMonthlyDataForTimeFrame('province', $startDate, $endDate)
                    ->where('category_type', 'Province')
                    ->join('provinces', 'applicant_monthly_data.category_id', '=', 'provinces.id')
                    ->select('provinces.name', DB::raw('SUM(applicant_monthly_data.count) as total_applicants'))
                    ->groupBy('provinces.name')
                    ->get()
                    ->map(function ($item) {
                        // Format for the chart
                        return ['x' => $item->name, 'y' => (int) $item->total_applicants];
                    })
                    ->toArray();
    }

    /**
     * Fetch applicants by race.
     *
     * This method retrieves the total number of applicants by race for the current year
     * from the monthly data associated with the given current year ID and query months.
     *
     * @param \DateTimeInterface|string $startDate The start date of the time frame.
     * @param \DateTimeInterface|string $endDate The end date of the time frame.
     *
     * @return \Illuminate\Support\Collection
     *     A collection containing the applicants by race for the current year.
     */
    public function getApplicantsByRace($startDate, $endDate)
    {
        return $this->getMonthlyDataForTimeFrame('Race', $startDate, $endDate)
            ->join('races', 'applicant_monthly_data.category_id', '=', 'races.id')
            ->get(['races.name as race_name', 'applicant_monthly_data.month', 'applicant_monthly_data.count']);
    }

    /**
     * Fetch applications per month.
     *
     * This method retrieves the total number of applications for a time selection
     *
     * @param \DateTimeInterface|string $startDate The start date of the time frame.
     * @param \DateTimeInterface|string $endDate The end date of the time frame.
     *
     * @return \Illuminate\Support\Collection
     *     A collection containing the applications for the current year.
     */
    public function getApplicationsCountPerMonth($startDate, $endDate)
    {
        return $this->getMonthlyDataForTimeFrame('Application', $startDate, $endDate)
            ->get(['month', 'count']);
    }

    /**
     * Fetch interviews per month.
     *
     * This method retrieves the total number of interviews for a time selection.
     *
     * @param \DateTimeInterface|string $startDate The start date of the time frame.
     * @param \DateTimeInterface|string $endDate The end date of the time frame.
     *
     * @return \Illuminate\Support\Collection
     *     A collection containing the interviews for the current year.
     */
    public function getInterviewsCountPerMonth($startDate, $endDate)
    {
        return $this->getMonthlyDataForTimeFrame('Interviewed', $startDate, $endDate)
            ->get(['month', 'count']);
    }

    /**
     * Fetch appointed applicants for the current year.
     *
     * This method retrieves the total number of appointed applicants for the current year
     * from the monthly data associated with the given current year ID and query months.
     *
     * @param \DateTimeInterface|string $startDate The start date of the time frame.
     * @param \DateTimeInterface|string $endDate The end date of the time frame.
     *
     * @return \Illuminate\Support\Collection
     *     A collection containing the appointed applicants for the current year.
     */
    public function getAppointedCountPerMonth($startDate, $endDate)
    {
        return $this->getMonthlyDataForTimeFrame('Appointed', $startDate, $endDate)
            ->get(['month', 'count']);
    }

    /**
     * Fetch rejected applicants for a time frame
     *
     * This method retrieves the total number of rejected applicants for the current year
     * from the monthly data associated with the given current year ID and query months.
     *
     * @param \DateTimeInterface|string $startDate The start date of the time frame.
     * @param \DateTimeInterface|string $endDate The end date of the time frame.
     *
     * @return \Illuminate\Support\Collection
     *     A collection containing the rejected applicants for the current year.
     */
    public function getRejectedApplicants($startDate, $endDate)
    {
        return $this->getMonthlyDataForTimeFrame('rejected', $startDate, $endDate)->get(['month', 'count']);
    }

    /**
     * Get the overall application completion rate.
     *
     * @param string|null $startDate The start date for filtering applications (optional).
     * @param string|null $endDate The end date for filtering applications (optional).
     *
     * @return float
     */
    public function getApplicationCompletionRate($startDate = null, $endDate = null)
    {
        $completeStateID = config('constants.complete_state_id');

        $query = Applicant::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalApplicants = $query->count();

        $completedCount = $query->where('state_id', '>=', $completeStateID)->count();

        $completedPercentage = ($totalApplicants > 0) ? ($completedCount / $totalApplicants) * 100 : 0;

        return number_format($completedPercentage, 2);
    }

    /**
     * Calculate the overall drop-off rate and drop-off rate by stage.
     *
     * @param string|null $startDate Optional start date for filtering applicants.
     * @param string|null $endDate Optional end date for filtering applicants.
     *
     * @return array An array containing the drop-off rate and drop-off rates by stage.
     */
    public function getDropOffRates($startDate = null, $endDate = null)
    {
        $completeStateID = config('constants.complete_state_id');

        $query = Applicant::query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $totalApplicants = $query->count();

        $dropoffCount = $query->where('state_id', '<', $completeStateID)->count();
        $dropoffPercentage = $totalApplicants > 0 ? ($dropoffCount / $totalApplicants) * 100 : 0;

        $stages = State::where('code', '!=', 'complete')->limit(5)->get();
        $dropoffByStage = [];

        foreach ($stages as $stage) {
            $stageDropoffCount = $query->where('state_id', $stage->id)->count();
            $dropoffByStage[$stage->code] = [
                'count' => $stageDropoffCount,
                'percentage' => $totalApplicants > 0 ? ($stageDropoffCount / $totalApplicants) * 100 : 0
            ];
        }

        return [
            'dropoff_rate' => [
                'count' => $dropoffCount,
                'percentage' => number_format($dropoffPercentage, 2),
            ],
            'dropoff_by_stage' => $dropoffByStage,
        ];
    }

    /**
     * Get total count of applicants in the talent pool (where appointed_id is null),
     * filtered by an optional date range.
     *
     * @param string|null $startDate Start date in 'Y-m-d' format
     * @param string|null $endDate End date in 'Y-m-d' format
     * @return int
     */
    public function getTotalTalentPoolCount(?string $startDate = null, ?string $endDate = null): int
    {
        $query = Applicant::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
        }

        return $query->count();
    }

    /**
     * Get total count of applicants who were appointed
     * filtered by an optional date range.
     *
     * @param string|null $startDate Start date in 'Y-m-d' format
     * @param string|null $endDate End date in 'Y-m-d' format
     * @return int
     */
    public function getTotalAppointedCount(?string $startDate = null, ?string $endDate = null): int
    {
        return $this->getTotalCountByType($startDate, $endDate, 'appointed');
    }

    /**
     * Get race breakdown (counts and percentages) of the talent pool,
     * filtered by an optional date range.
     *
     * @param string|null $startDate Start date in 'Y-m-d' format
     * @param string|null $endDate End date in 'Y-m-d' format
     * @return array
     * [
     *   'counts' => \Illuminate\Database\Eloquent\Collection, // Collection of race counts
     *   'percentages' => array  // Array of race percentages with race_id as key
     * ]
     */
    public function getRaceBreakdown(?string $startDate = null, ?string $endDate = null, string $type = 'talent_pool'): array
    {
        $totalTalentPool = $this->getTotalTalentPoolCount($startDate, $endDate);

        $query = Applicant::join('races', 'applicants.race_id', '=', 'races.id')
            ->select('races.name', DB::raw('count(*) as count'))
            ->groupBy('races.name');

        if ($type === 'talent_pool') {
            $query->whereNull('appointed_id');
        } elseif ($type === 'appointed') {
            $query->whereNotNull('appointed_id');
        } elseif ($type === 'interviewed') {
            $query->whereHas('interviews');
        }

        if ($startDate && $endDate) {
            $query->whereBetween('applicants.created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
        }

        $raceCounts = $query->get();

        $racePercentages = [];
        foreach ($raceCounts as $raceCount) {
            $racePercentages[$raceCount->name] = ($raceCount->count / $totalTalentPool) * 100;
        }

        return [
            'counts' => $raceCounts,
            'percentages' => $racePercentages
        ];
    }

    /**
     * Get gender breakdown (counts and percentages) of the talent pool,
     * filtered by an optional date range.
     *
     * @param string|null $startDate Start date in 'Y-m-d' format
     * @param string|null $endDate End date in 'Y-m-d' format
     * @return array
     * [
     *   'male_count' => int,  // Total count of male applicants
     *   'female_count' => int,  // Total count of female applicants
     *   'male_percentage' => float,  // Percentage of male applicants
     *   'female_percentage' => float  // Percentage of female applicants
     * ]
     */
    public function getGenderBreakdown(?string $startDate = null, ?string $endDate = null): array
    {
        $totalTalentPool = $this->getTotalTalentPoolCount($startDate, $endDate);

        $maleQuery = Applicant::whereNull('appointed_id')->where('gender_id', 1);
        $femaleQuery = Applicant::whereNull('appointed_id')->where('gender_id', 2);

        if ($startDate && $endDate) {
            $maleQuery->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
            $femaleQuery->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
        }

        $maleCount = $maleQuery->count();
        $femaleCount = $femaleQuery->count();

        return [
            'counts' => [
                'male_count' => $maleCount,
                'female_count' => $femaleCount,
            ],
            'percentages' => [
                'male_percentage' => ($maleCount / $totalTalentPool) * 100,
                'female_percentage' => ($femaleCount / $totalTalentPool) * 100
            ],
        ];
    }

    /**
     * Get age breakdown (counts and percentages) of the talent pool or specific types of applicants,
     * filtered by an optional date range and application type.
     *
     * @param string|null $startDate Start date in 'Y-m-d' format
     * @param string|null $endDate End date in 'Y-m-d' format
     * @param string $type The type of applicants to filter ('talent_pool', 'appointed', 'interviewed')
     * @return array
     * [
     *   'counts' => array,  // Array of age group counts
     *   'percentages' => array  // Array of age group percentages
     * ]
     */
    public function getAgeBreakdown(?string $startDate = null, ?string $endDate = null, string $type = 'talent_pool'): array
    {
        $totalApplicants = $this->getTotalCountByType($startDate, $endDate, $type);

        $ageGroups = [
            '18-24' => [18, 24],
            '25-30' => [25, 30],
            '31-40' => [31, 40],
            '41-50' => [41, 50],
            '51-60' => [51, 60],
            '60+'   => [60, 100],
        ];

        $ageCounts = [];
        $agePercentages = [];

        foreach ($ageGroups as $group => $range) {
            $query = Applicant::query();

            if ($type === 'talent_pool') {
                $query->whereNull('appointed_id');
            } elseif ($type === 'appointed') {
                $query->whereNotNull('appointed_id');
            } elseif ($type === 'interviewed') {
                $query->whereHas('interviews');
            }

            $query->whereBetween('age', [$range[0], $range[1]]);

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
            }

            $count = $query->count();
            $ageCounts[$group] = $count;

            if ($totalApplicants > 0) {
                $agePercentages[$group] = ($count / $totalApplicants) * 100;
            } else {
                $agePercentages[$group] = 0;
            }
        }

        return [
            'counts' => $ageCounts,
            'percentages' => $agePercentages
        ];
    }

    /**
     * Get the total count of applicants based on the type and optional date range.
     *
     * @param string|null $startDate Start date in 'Y-m-d' format
     * @param string|null $endDate End date in 'Y-m-d' format
     * @param string $type The type of applicants ('talent_pool', 'appointed', 'interviewed')
     * @return int
     */
    protected function getTotalCountByType(?string $startDate = null, ?string $endDate = null, string $type): int
    {
        $query = Applicant::query();

        if ($type === 'all') {
            $query->whereNull('appointed_id');
        } elseif ($type === 'appointed') {
            $query->whereNotNull('appointed_id');
        } elseif ($type === 'interviewed') {
            $query->whereHas('interviews');
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
        }

        return $query->count();
    }

    /**
     * Get completion and drop-off rates segmented by geographic region.
     *
     * @param string|null $startDate The start date for filtering applicants (optional).
     * @param string|null $endDate The end date for filtering applicants (optional).
     *
     * @return array An array containing completion and drop-off rates by region.
     */
    // public function getCompletionByRegion($startDate = null, $endDate = null)
    // {
    //     $completeStateID = config('constants.complete_state_id');

    //     $regions = Province::all();

    //     $completionByRegion = [];

    //     foreach ($regions as $region) {
    //         $query = Applicant::where('province_id', $region->id);

    //         if ($startDate) {
    //             $query->where('created_at', '>=', $startDate);
    //         }
    //         if ($endDate) {
    //             $query->where('created_at', '<=', $endDate);
    //         }

    //         $totalRegionApplicants = $query->count();

    //         $completedRegionCount = $query->where('state_id', '>=', $completeStateID)->count();

    //         $dropoffRegionCount = $query->where('state_id', '<', $completeStateID)->count();

    //         $completedPercentage = $totalRegionApplicants > 0
    //             ? ($completedRegionCount / $totalRegionApplicants) * 100
    //             : 0;

    //         $dropoffPercentage = $totalRegionApplicants > 0
    //             ? ($dropoffRegionCount / $totalRegionApplicants) * 100
    //             : 0;

    //         $completionByRegion[$region->name] = [
    //             'completed_count' => $completedRegionCount,
    //             'completed_percentage' => $completedPercentage,
    //             'dropoff_count' => $dropoffRegionCount,
    //             'dropoff_percentage' => $dropoffPercentage,
    //         ];
    //     }

    //     return $completionByRegion;
    // }

    /**
     * Fetch placed applicants with their assessment scores, brand, and province
     * for a given start and end date range.
     *
     * @param \DateTimeInterface|string $startDate The start date of the date range.
     * @param \DateTimeInterface|string $endDate The end date of the date range.
     *
     * @return \Illuminate\Support\Collection
     *     A collection containing placed applicants and their assessment scores within the date range.
     */
    public function getPlacedApplicantsWithScoresByDateRange($startDate, $endDate)
    {
        $startDate = $startDate instanceof \DateTimeInterface ? $startDate : Carbon::parse($startDate);
        $endDate = $endDate instanceof \DateTimeInterface ? $endDate : Carbon::parse($endDate);

        return DB::table('vacancy_fills')
            ->join('applicants', 'vacancy_fills.applicant_id', '=', 'applicants.id')
            ->join('vacancies', 'vacancy_fills.vacancy_id', '=', 'vacancies.id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->join('towns', 'applicants.town_id', '=', 'towns.id')
            ->select(
                'applicants.literacy_score',
                'applicants.numeracy_score',
                'stores.brand_id',
                'towns.province_id'
            )
            ->whereBetween('vacancy_fills.created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();
    }

    /**
     * Fetch placed applicants with their assessment scores, brand, and division
     * for a given start and end date range.
     *
     * @param int $divisionId The division ID to filter by.
     * @param \DateTimeInterface|string $startDate The start date of the date range.
     * @param \DateTimeInterface|string $endDate The end date of the date range.
     *
     * @return \Illuminate\Support\Collection
     *     A collection containing placed applicants and their assessment scores within the date range.
     */
    public function getPlacedApplicantsWithScoresByDivisionAndDateRange($divisionId, $startDate, $endDate)
    {
        $startDate = $startDate instanceof \DateTimeInterface ? $startDate : Carbon::parse($startDate);
        $endDate = $endDate instanceof \DateTimeInterface ? $endDate : Carbon::parse($endDate);

        return DB::table('vacancy_fills')
            ->join('applicants', 'vacancy_fills.applicant_id', '=', 'applicants.id')
            ->join('vacancies', 'vacancy_fills.vacancy_id', '=', 'vacancies.id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->join('towns', 'applicants.town_id', '=', 'towns.id')
            ->join('divisions', 'stores.division_id', '=', 'divisions.id')
            ->select(
                'applicants.literacy_score',
                'applicants.numeracy_score',
                'stores.brand_id',
                'stores.division_id'
            )
            ->where('stores.division_id', '=', $divisionId)
            ->whereBetween('vacancy_fills.created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();
    }

    /**
     * Fetch placed applicants with their assessment scores, brand, and region
     * for a given start and end date range.
     *
     * @param int $regionId The region ID to filter by.
     * @param \DateTimeInterface|string $startDate The start date of the date range.
     * @param \DateTimeInterface|string $endDate The end date of the date range.
     *
     * @return \Illuminate\Support\Collection
     *     A collection containing placed applicants and their assessment scores within the date range.
     */
    public function getPlacedApplicantsWithScoresByRegionAndDateRange($regionId, $startDate, $endDate)
    {
        $startDate = $startDate instanceof \DateTimeInterface ? $startDate : Carbon::parse($startDate);
        $endDate = $endDate instanceof \DateTimeInterface ? $endDate : Carbon::parse($endDate);

        return DB::table('vacancy_fills')
            ->join('applicants', 'vacancy_fills.applicant_id', '=', 'applicants.id')
            ->join('vacancies', 'vacancy_fills.vacancy_id', '=', 'vacancies.id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->join('towns', 'applicants.town_id', '=', 'towns.id')
            ->join('regions', 'stores.region_id', '=', 'regions.id')
            ->select(
                'applicants.literacy_score',
                'applicants.numeracy_score',
                'stores.brand_id',
                'stores.region_id'
            )
            ->where('stores.region_id', '=', $regionId)
            ->whereBetween('vacancy_fills.created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();
    }

    /**
     * Fetch placed applicants with their assessment scores, brand, and store
     * for a given start and end date range.
     *
     * @param int $storeId The store ID to filter by.
     * @param \DateTimeInterface|string $startDate The start date of the date range.
     * @param \DateTimeInterface|string $endDate The end date of the date range.
     *
     * @return \Illuminate\Support\Collection
     *     A collection containing placed applicants and their assessment scores within the date range.
     */
    public function getPlacedApplicantsWithScoresForStoreAndDateRange($storeId, $startDate, $endDate)
    {
        $startDate = $startDate instanceof \DateTimeInterface ? $startDate : Carbon::parse($startDate);
        $endDate = $endDate instanceof \DateTimeInterface ? $endDate : Carbon::parse($endDate);

        return DB::table('vacancy_fills')
            ->join('applicants', 'vacancy_fills.applicant_id', '=', 'applicants.id')
            ->join('vacancies', 'vacancy_fills.vacancy_id', '=', 'vacancies.id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->join('towns', 'applicants.town_id', '=', 'towns.id')
            ->select(
                'applicants.literacy_score',
                'applicants.numeracy_score',
                'stores.brand_id',
                'stores.id'
            )
            ->where('stores.id', '=', $storeId)
            ->whereBetween('vacancy_fills.created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();
    }

    /**
     * This method calculates the average literacy, numeracy, and situational scores for each brand
     * based on placed applicants' data. It also converts these averages into percentages.
     *
     * @param \Illuminate\Support\Collection $placedApplicants A collection of placed applicants.
     *
     * @return array
     *     An array containing average literacy, numeracy, and situational percentages per brand.
     */
    public function calculateAverageScoresByBrand($placedApplicants)
    {
        $brands = DB::table('brands')->get();

        $averageScoresByBrand = [];

        foreach ($brands as $brand) {
            $brandApplicants = $placedApplicants->where('brand_id', $brand->id);

            if ($brandApplicants->isNotEmpty()) {
                $avgLiteracyScore = $brandApplicants->avg('literacy_score');
                $avgNumeracyScore = $brandApplicants->avg('numeracy_score');

                $literacyPercentage = ($avgLiteracyScore / 10) * 100;
                $numeracyPercentage = ($avgNumeracyScore / 10) * 100;

                $averageScoresByBrand[$brand->name] = [
                    'literacy_percentage' => round($literacyPercentage, 2),
                    'numeracy_percentage' => round($numeracyPercentage, 2),
                ];
            }
        }

        return $averageScoresByBrand;
    }

    /**
     * This method calculates the average literacy, numeracy, and situational scores for each province
     * based on placed applicants' data. It also converts these averages into percentages.
     *
     * @param \Illuminate\Support\Collection $placedApplicants A collection of placed applicants.
     *
     * @return array
     *     An array containing average literacy, numeracy, and situational percentages per province.
     */
    public function calculateAverageScoresByProvince($placedApplicants)
    {
        $provinces = Province::all();

        $averageScoresByProvince = [];

        foreach ($provinces as $province) {
            $provinceApplicants = $placedApplicants->where('province_id', $province->id);

            if ($provinceApplicants->isNotEmpty()) {
                $avgLiteracyScore = $provinceApplicants->avg('literacy_score');
                $avgNumeracyScore = $provinceApplicants->avg('numeracy_score');

                $literacyPercentage = ($avgLiteracyScore / 10) * 100;
                $numeracyPercentage = ($avgNumeracyScore / 10) * 100;

                $averageScoresByProvince[$province->name] = [
                    'literacy_percentage' => round($literacyPercentage, 2),
                    'numeracy_percentage' => round($numeracyPercentage, 2),
                ];
            }
        }

        return $averageScoresByProvince;
    }

    /**
     * Calculate the average assessment scores for all placed applicants.
     *
     * @param \Illuminate\Support\Collection $placedApplicants
     *     A collection of placed applicants with their assessment scores.
     *
     * @return array|null
     *     Returns an array containing the average literacy, numeracy, and situational scores.
     *     If no applicants are found, it returns null.
     */
    public function calculateAverageScores($placedApplicants)
    {
        if ($placedApplicants->isNotEmpty()) {
            $avgLiteracyScore = $placedApplicants->avg('literacy_score');
            $avgNumeracyScore = $placedApplicants->avg('numeracy_score');
            $avgSituationalScore = $placedApplicants->avg('situational_score');

            return [
                'avg_literacy_score' => round($avgLiteracyScore, 2),
                'avg_numeracy_score' => round($avgNumeracyScore, 2),
            ];
        }

        return null;
    }

    /**
     * Fetch monthly data for a specific time frame.
     *
     * This method retrieves the monthly data for a specified time frame
     * from the monthly data associated with the given category type and year.
     *
     * @param string $categoryType The category type of the data.
     * @param \DateTimeInterface|string $startDate The start date of the time frame.
     * @param \DateTimeInterface|string $endDate The end date of the time frame.
     *
     * @return \Illuminate\Support\Collection
     *     A collection containing the monthly data for the specified time frame, category type, and year.
     */
    protected function getMonthlyDataForTimeFrame($categoryType, $startDate, $endDate)
    {
        // Convert string dates to DateTime objects if necessary
        $startDate = $startDate instanceof \DateTimeInterface ? $startDate : new \DateTime($startDate);
        $endDate = $endDate instanceof \DateTimeInterface ? $endDate : new \DateTime($endDate);

        $startOfDay = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        return ApplicantMonthlyData::where('category_type', $categoryType)
            ->where('applicant_monthly_data.created_at', '>=', $startOfDay)
            ->where('applicant_monthly_data.created_at', '<=', $endDate);
    }

    protected function handlePreviousYearData($currentMonth, $months, $previousYearData, &$totalApplicantsPerMonth)
    {
        foreach (array_slice($months, $currentMonth + 1) as $month) {
            $monthKey = strtolower($month);
            $totalApplicantsPerMonth[] = $previousYearData->$monthKey ?? 0;
        }
    }

    protected function handleCurrentYearData($currentMonth, $months, $currentYearData, &$totalApplicantsPerMonth)
    {
        foreach (array_slice($months, 0, $currentMonth + 1) as $month) {
            $monthKey = strtolower($month);
            $totalApplicantsPerMonth[] = $currentYearData->$monthKey ?? 0;
        }
    }
}
