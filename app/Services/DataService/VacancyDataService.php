<?php

namespace App\Services\DataService;

use Carbon\Carbon;
use App\Models\Store;
use App\Models\Vacancy;
use App\Models\Interview;
use App\Models\Shortlist;
use Illuminate\Support\Facades\DB;

class VacancyDataService
{
    /**
     * Get total number of vacancies for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getStoreTotalVacancies(int $storeId, $startDate, $endDate): int
    {
        // Count all vacancies where store_id matches and created_at falls within the date range
        return Vacancy::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Get total number of filled vacancies for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getStoreTotalVacanciesFilled(int $storeId, $startDate, $endDate): int
    {
        // Count all vacancies where store_id matches, open_position is 0, and created_at falls within the date range
        return Vacancy::where('store_id', $storeId)
            ->where('open_positions', 0)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Get total number of scheduled interviews for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getStoreTotalInterviewsScheduled(int $storeId, $startDate, $endDate): int
    {
        // Retrieve all interviews where interview->vacancy->store_id = $storeId and created_at falls within the date range
        return Interview::whereHas('vacancy', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Get total number of completed interviews for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getStoreTotalInterviewsCompleted(int $storeId, $startDate, $endDate): int
    {
        // Retrieve all interviews where interview->vacancy->store_id = $storeId, score is not null, and created_at falls within the date range
        return Interview::whereHas('vacancy', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->whereNotNull('score')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Get total number of completed interviews for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getStoreTotalApplicantsAppointed(int $storeId, $startDate, $endDate): int
    {
        // Retrieve all applicants where vacancy->store_id = $storeId, status is 'Appointed', and created_at falls within the date range
        return Interview::whereHas('vacancy', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->where('status', 'Appointed') // Filter by status 'Appointed'
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Get total number of completed interviews for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getStoreTotalApplicantsRegretted(int $storeId, $startDate, $endDate): int
    {
        // Retrieve all applicants where vacancy->store_id = $storeId, status is 'Regretted', and created_at falls within the date range
        return Interview::whereHas('vacancy', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->where('status', 'Regretted') // Filter by status 'Regretted'
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Calculate the average time to shortlist for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return string
     */
    public function getStoreAverageTimeToShortlist(int $storeId, $startDate, $endDate)
    {
        // Retrieve all vacancies for the store within the specified date range
        $vacancies = Vacancy::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('shortlists') // Load the shortlists relationship
            ->get();

        $totalDays = 0;
        $totalHours = 0;
        $totalMinutes = 0;
        $totalSeconds = 0;
        $shortlistCount = 0;

        // Loop through each vacancy and its associated shortlists
        foreach ($vacancies as $vacancy) {
            foreach ($vacancy->shortlists as $shortlist) {
                // Calculate the time difference between vacancy creation and shortlist creation
                $interval = $vacancy->created_at->diff($shortlist->created_at);

                // Add to total time
                $totalDays += $interval->days;
                $totalHours += $interval->h;
                $totalMinutes += $interval->i;
                $totalSeconds += $interval->s;
                $shortlistCount++;
            }
        }

        // Calculate the average time to shortlist
        if ($shortlistCount > 0) {
            // Normalize seconds to minutes, minutes to hours, and hours to days
            $totalMinutes += floor($totalSeconds / 60);
            $totalSeconds = $totalSeconds % 60; // Remainder for seconds

            $totalHours += floor($totalMinutes / 60);
            $totalMinutes = $totalMinutes % 60; // Remainder for minutes

            $totalDays += floor($totalHours / 24);
            $totalHours = $totalHours % 24; // Remainder for hours

            // If the total time is less than one hour, return only minutes and seconds
            if ($totalDays == 0 && $totalHours == 0) {
                return sprintf('%dM %dS', $totalMinutes, $totalSeconds);
            }

            // If the total time is less than one day, return only hours and minutes
            if ($totalDays == 0) {
                return sprintf('%dH %dM', $totalHours, $totalMinutes);
            }

            // Otherwise, return the full format (days, hours, and minutes)
            return sprintf('%dD %dH %dM', $totalDays, $totalHours, $totalMinutes);
        } else {
            return '0D 0H 0M'; // Return default format if no shortlists
        }
    }

    /**
     * Calculate the average time to hire for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return string
     */
    public function getStoreAverageTimeToHire(int $storeId, $startDate, $endDate)
    {
        // Retrieve all vacancies for the store within the specified date range
        $vacancies = Vacancy::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['appointed' => function($query) {
                $query->orderBy('vacancy_fills.created_at', 'asc');
            }]) // Load the appointed relationship, ordered by the first appointment
            ->get();

        $totalDays = 0;
        $totalHours = 0;
        $totalMinutes = 0;
        $totalSeconds = 0;
        $hiringCount = 0;

        // Loop through each vacancy and calculate the time difference for the first appointed record
        foreach ($vacancies as $vacancy) {
            if ($vacancy->appointed->isNotEmpty()) {
                // Get the first appointment record
                $firstAppointed = $vacancy->appointed->first();

                // Calculate the time difference between vacancy creation and the first appointment
                $interval = $vacancy->created_at->diff($firstAppointed->pivot->created_at);

                // Add to total time
                $totalDays += $interval->days;
                $totalHours += $interval->h;
                $totalMinutes += $interval->i;
                $totalSeconds += $interval->s;
                $hiringCount++;
            }
        }

        // Calculate the average time to hire
        if ($hiringCount > 0) {
            // Normalize seconds to minutes, minutes to hours, and hours to days
            $totalMinutes += floor($totalSeconds / 60);
            $totalSeconds = $totalSeconds % 60; // Remainder for seconds

            $totalHours += floor($totalMinutes / 60);
            $totalMinutes = $totalMinutes % 60; // Remainder for minutes

            $totalDays += floor($totalHours / 24);
            $totalHours = $totalHours % 24; // Remainder for hours

            // If the total time is less than one hour, return only minutes and seconds
            if ($totalDays == 0 && $totalHours == 0) {
                return sprintf('%dM %dS', $totalMinutes, $totalSeconds);
            }

            // If the total time is less than one day, return only hours and minutes
            if ($totalDays == 0) {
                return sprintf('%dH %dM', $totalHours, $totalMinutes);
            }

            // Otherwise, return the full format (days, hours, and minutes)
            return sprintf('%dD %dH %dM', $totalDays, $totalHours, $totalMinutes);
        } else {
            return '0D 0H 0M'; // Return default format if no appointments
        }
    }

    /**
     * Get the total number of applicants appointed within a date range for vacancies where store_id = $storeId.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getStoreApplicantsAppointed(int $storeId, $startDate, $endDate)
    {
        // Retrieve all vacancies for the store within the date range
        $vacancies = Vacancy::where('store_id', $storeId)
            ->with(['appointed' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]); // Only fetch appointed within the date range
            }])
            ->get();

        // Initialize the total count
        $totalAppointed = 0;

        // Loop through the vacancies and count the appointed applicants
        foreach ($vacancies as $vacancy) {
            $totalAppointed += $vacancy->appointed->count();
        }

        return $totalAppointed;
    }

    /**
     * Get the number of appointed applicants by month for vacancies where store_id = $storeId.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    public function getStoreApplicantsAppointedByMonth(int $storeId, $startDate, $endDate)
    {
        // Initialize an array to hold the results, with months set to 0 from startDate to endDate
        $applicantsByMonth = [];
        $currentDate = $startDate->copy();

        // Loop to populate only the months between startDate and endDate
        while ($currentDate->lte($endDate)) {
            $monthName = $currentDate->format('M');
            $applicantsByMonth[$monthName] = 0;
            $currentDate->addMonth();
        }

        // Retrieve all vacancies for the store within the date range
        $vacancies = Vacancy::where('store_id', $storeId)
            ->with(['appointed' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]); // Only fetch appointed within the date range
            }])
            ->get();

        // Group appointed applicants by the month of their appointment date and count them
        foreach ($vacancies as $vacancy) {
            foreach ($vacancy->appointed as $applicant) {
                // Get the month name from the created_at date in the vacancy_fills table (the appointment date)
                $month = $applicant->pivot->created_at->format('M');
                // Increment the count for the corresponding month
                if (isset($applicantsByMonth[$month])) {
                    $applicantsByMonth[$month]++;
                }
            }
        }

        return $applicantsByMonth;
    }

    /**
     * Calculate the average time to shortlist for all vacancies (nationwide),
     * within an optional date range.
     *
     * @param string|null $startDate Start date in 'Y-m-d' format
     * @param string|null $endDate End date in 'Y-m-d' format
     * @return float|null The average time to shortlist
     */
    public function getNationwideAverageTimeToShortlist(?string $startDate = null, ?string $endDate = null): ?float
    {
        $query = DB::table('vacancies')
            ->join('shortlists', 'vacancies.id', '=', 'shortlists.vacancy_id')
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, shortlists.created_at))) as avg_time_to_shortlist'));

        if ($startDate && $endDate) {
            $query->whereBetween('vacancies.created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
        }

        return $query->value('avg_time_to_shortlist');
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
     * Calculate the nationwide average time to hire, within an optional date range.
     *
     * @param string|null $startDate Start date in 'Y-m-d' format
     * @param string|null $endDate End date in 'Y-m-d' format
     * @return float|null The average time to hire
     */
    public function getNationwideAverageTimeToHire(?string $startDate = null, ?string $endDate = null): ?float
    {
        $query = DB::table('vacancies')
            ->join('vacancy_fills', 'vacancies.id', '=', 'vacancy_fills.vacancy_id')
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, vacancy_fills.created_at))) as avg_time_to_hire'));

        if ($startDate && $endDate) {
            $query->whereBetween('vacancies.created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
        }

        return $query->value('avg_time_to_hire');
    }

    /**
     * Calculate the time to hire for all vacancies in a specific region.
     *
     * @param int $regionId
     * @param string|null $startDate Start date in 'Y-m-d' format
     * @param string|null $endDate End date in 'Y-m-d' format
     * @return float|null The average time to hire
     */
    public function getRegionWideAverageTimeToHire(int $regionId, ?string $startDate = null, ?string $endDate = null): ?float
    {
        $query = DB::table('vacancies')
            ->join('vacancy_fills', 'vacancies.id', '=', 'vacancy_fills.vacancy_id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->where('stores.region_id', $regionId)
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, vacancy_fills.created_at))) as avg_time_to_hire'));

        if ($startDate && $endDate) {
            $query->whereBetween('vacancies.created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
        }

        return $query->value('avg_time_to_hire');
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
