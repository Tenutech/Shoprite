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
    public function getStoreTotalVacancies(int $storeId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalVacancies('store', $storeId, $startDate, $endDate);
    }

    /**
     * Get total number of filled vacancies for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getStoreTotalVacanciesFilled(int $storeId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalVacanciesFilled('store', $storeId, $startDate, $endDate);
    }

    /**
     * Get total number of scheduled interviews for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getStoreTotalInterviewsScheduled(int $storeId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalInterviewsScheduled('store', $storeId, $startDate, $endDate);
    }

    /**
     * Get total number of completed interviews for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getStoreTotalInterviewsCompleted(int $storeId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalInterviewsCompleted('store', $storeId, $startDate, $endDate);
    }

    /**
     * Get total number of completed interviews for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getStoreTotalApplicantsAppointed(int $storeId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalApplicantsAppointed('store', $storeId, $startDate, $endDate);
    }

    /**
     * Get total number of completed interviews for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getStoreTotalApplicantsRegretted(int $storeId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalApplicantsRegretted('store', $storeId, $startDate, $endDate);
    }

    /**
     * Calculate the average time to shortlist for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return string
     */
    public function getStoreAverageTimeToShortlist(int $storeId, Carbon $startDate, Carbon $endDate)
    {
        return $this->getAverageTimeToShortlist('store', $storeId, $startDate, $endDate);
    }

    /**
     * Calculate the average time to hire for a specific store within a date range.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return string
     */
    public function getStoreAverageTimeToHire(int $storeId, Carbon $startDate, Carbon $endDate)
    {
        return $this->getAverageTimeToHire('store', $storeId, $startDate, $endDate);
    }

    /**
     * Get the total number of applicants appointed within a date range for vacancies where store_id = $storeId.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getStoreApplicantsAppointed(int $storeId, Carbon $startDate, Carbon $endDate)
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
     * Get total number of vacancies for a specific division within a date range.
     *
     * @param int $divisionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getDivisionTotalVacancies(int $divisionId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalVacancies('division', $divisionId, $startDate, $endDate);
    }

    /**
     * Get total number of filled vacancies for a specific division within a date range.
     *
     * @param int $divisionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getDivisionTotalVacanciesFilled(int $divisionId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalVacanciesFilled('division', $divisionId, $startDate, $endDate);
    }

    /**
     * Get total number of scheduled interviews for a specific division within a date range.
     *
     * @param int $divisionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getDivisionTotalInterviewsScheduled(int $divisionId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalInterviewsScheduled('division', $divisionId, $startDate, $endDate);
    }

    /**
     * Get total number of scheduled interviews for a specific division within a date range.
     *
     * @param int $divisionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getDivisionTotalInterviewsCompleted(int $divisionId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalInterviewsCompleted('division', $divisionId, $startDate, $endDate);
    }

    /**
     * Get total number of appointed applicants for a specific division within a date range.
     *
     * @param int $divisionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getDivisionTotalApplicantsAppointed(int $divisionId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalApplicantsAppointed('division', $divisionId, $startDate, $endDate);
    }

    /**
     * Get total number of completed interviews for a specific division within a date range.
     *
     * @param int $divisionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getDivisionTotalApplicantsRegretted(int $divisionId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalApplicantsRegretted('division', $divisionId, $startDate, $endDate);
    }

    /**
     * Calculate the average time to shortlist for a specific store within a date range.
     *
     * @param int $divisionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return string
     */
    public function getDivisionAverageTimeToShortlist(int $divisionId, Carbon $startDate, Carbon $endDate)
    {
        return $this->getAverageTimeToShortlist('division', $divisionId, $startDate, $endDate);
    }

    /**
     * Calculate the average time to hire for a specific store within a date range.
     *
     * @param int $divisionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return string
     */
    public function getDivisionAverageTimeToHire(int $divisionId, Carbon $startDate, Carbon $endDate)
    {
        return $this->getAverageTimeToHire('division', $divisionId, $startDate, $endDate);
    }

    /**
     * Get total number of vacancies for a specific region within a date range.
     *
     * @param int $regionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getRegionTotalVacancies(int $regionId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalVacancies('region', $regionId, $startDate, $endDate);
    }

    /**
    * Get total number of filled vacancies for a specific region within a date range.
    *
    * @param int $regionId
    * @param \Carbon\Carbon $startDate
    * @param \Carbon\Carbon $endDate
    * @return int
    */
    public function getRegionTotalVacanciesFilled(int $regionId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalVacanciesFilled('region', $regionId, $startDate, $endDate);
    }

    /**
    * Get total number of scheduled interviews for a specific region within a date range.
    *
    * @param int $regionId
    * @param \Carbon\Carbon $startDate
    * @param \Carbon\Carbon $endDate
    * @return int
    */
    public function getRegionTotalInterviewsScheduled(int $regionId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalInterviewsScheduled('region', $regionId, $startDate, $endDate);
    }

    /**
     * Get total number of scheduled interviews for a specific region within a date range.
     *
     * @param int $regionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getRegionTotalInterviewsCompleted(int $regionId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalInterviewsCompleted('region', $regionId, $startDate, $endDate);
    }

    /**
     * Get total number of appointed applicants for a specific region within a date range.
     *
     * @param int $regionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getRegionTotalApplicantsAppointed(int $regionId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalApplicantsAppointed('region', $regionId, $startDate, $endDate);
    }

    /**
     * Get total number of completed interviews for a specific region within a date range.
     *
     * @param int $regionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getRegionTotalApplicantsRegretted(int $regionId, Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalApplicantsRegretted('region', $regionId, $startDate, $endDate);
    }

    /**
     * Calculate the average time to shortlist for a specific store within a date range.
     *
     * @param int $regionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return string
     */
    public function getRegionAverageTimeToShortlist(int $regionId, Carbon $startDate, Carbon $endDate)
    {
        return $this->getAverageTimeToShortlist('region', $regionId, $startDate, $endDate);
    }

    /**
     * Calculate the average time to hire for a specific store within a date range.
     *
     * @param int $regionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return string
     */
    public function getRegionAverageTimeToHire(int $regionId, Carbon $startDate, Carbon $endDate)
    {
        return $this->getAverageTimeToHire('region', $regionId, $startDate, $endDate);
    }

    /**
    * Get total number of vacancies for a date range.
    *
    * @param \Carbon\Carbon $startDate
    * @param \Carbon\Carbon $endDate
    * @return int
    */
    public function getAllTotalVacancies(Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalVacancies(null, null, $startDate, $endDate);
    }

    /**
    * Get total number of filled vacancies for a date range.
    *
    * @param \Carbon\Carbon $startDate
    * @param \Carbon\Carbon $endDate
    * @return int
    */
    public function getAllTotalVacanciesFilled(Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalVacanciesFilled(null, null, $startDate, $endDate);
    }

    /**
    * Get total number of scheduled interviews for a specific region within a date range.
    *
    * @param \Carbon\Carbon $startDate
    * @param \Carbon\Carbon $endDate
    * @return int
    */
    public function getAllTotalInterviewsScheduled(Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalInterviewsScheduled(null, null, $startDate, $endDate);
    }

    /**
     * Get total number of scheduled interviews within a date range.
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getallTotalInterviewsCompleted(Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalInterviewsCompleted(null, null, $startDate, $endDate);
    }

    /**
     * Get total number of appointed applicants within a date range.
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getAllTotalApplicantsAppointed(Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalApplicantsAppointed(null, null, $startDate, $endDate);
    }

    /**
     * Get total number of completed interviews within a date range.
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    public function getAllTotalApplicantsRegretted(Carbon $startDate, Carbon $endDate): int
    {
        return $this->getTotalApplicantsRegretted(null, null, $startDate, $endDate);
    }

    /**
     * Calculate the average time to shortlist for a specific store within a date range.
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return string
     */
    public function getAllAverageTimeToShortlist(Carbon $startDate, Carbon $endDate)
    {
        return $this->getAverageTimeToShortlist(null, null, $startDate, $endDate);
    }

    /**
     * Calculate the average time to hire for a specific store within a date range.
     *
     * @param int $regionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return string
     */
    public function getAllAverageTimeToHire(Carbon $startDate, Carbon $endDate)
    {
        return $this->getAverageTimeToHire(null, null, $startDate, $endDate);
    }

    /**
     * Calculate the average time to shortlist for all vacancies (nationwide),
     * within an optional date range.
     *
     * @param string|null $startDate Start date in 'Y-m-d' format
     * @param string|null $endDate End date in 'Y-m-d' format
     * @return float|null The average time to shortlist
     */
    public function getNationwideAverageTimeToShortlist(Carbon $startDate = null, Carbon $endDate = null): ?float
    {
        $query = DB::table('vacancies')
            ->join('shortlists', 'vacancies.id', '=', 'shortlists.vacancy_id')
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, shortlists.created_at))) as avg_time_to_shortlist'));

        if ($startDate && $endDate) {
            $query->whereBetween('vacancies.created_at', [$startDate, $endDate]);
        }

        return $query->value('avg_time_to_shortlist');
    }

    /**
     * Calculate the nationwide average time to hire, within an optional date range.
     *
     * @param string|null $startDate Start date in 'Y-m-d' format
     * @param string|null $endDate End date in 'Y-m-d' format
     * @return float|null The average time to hire
     */
    public function getNationwideAverageTimeToHire(Carbon $startDate = null, Carbon $endDate = null): ?float
    {
        $query = DB::table('vacancies')
            ->join('vacancy_fills', 'vacancies.id', '=', 'vacancy_fills.vacancy_id')
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, vacancies.created_at, vacancy_fills.created_at))) as avg_time_to_hire'));

        if ($startDate && $endDate) {
            $query->whereBetween('vacancies.created_at', [$startDate, $endDate]);
        }

        return $query->value('avg_time_to_hire');
    }

    /**
     * Fetch the total vacancies
     *
     * @param string $type The type of view (e.g., national, division, area, store).
     * @param int|null $id The ID for filtering based on the type.
     * @param string $startDate The start date for filtering applicants.
     * @param string $endDate The end date for filtering applicants.
     * @return int The total count of vacancies
     */
    protected function getTotalVacancies(string $type = null, ?int $id = null, string $startDate, string $endDate)
    {
        $query = DB::table('vacancies')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->whereBetween('vacancies.created_at', [$startDate, $endDate]);

        if ($type === 'division') {
            $query->where('stores.division_id', $id);
        } elseif ($type === 'region') {
            $query->where('stores.region_id', $id);
        } elseif ($type === 'store') {
            $query->where('stores.id', $id);
        }

        return $query->count();
    }

    /**
     * Fetch the total vacancies filled
     *
     * @param string $type The type of view (e.g., national, division, area, store).
     * @param int|null $id The ID for filtering based on the type.
     * @param string $startDate The start date for filtering applicants.
     * @param string $endDate The end date for filtering applicants.
     * @return int The total count of vacancies
     */
    protected function getTotalVacanciesFilled(string $type = null, ?int $id = null, string $startDate, string $endDate)
    {
        $query = DB::table('vacancies')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->where('open_positions', 0)
            ->whereBetween('vacancies.created_at', [$startDate, $endDate]);

        if ($type === 'division') {
            $query->where('stores.division_id', $id);
        } elseif ($type === 'region') {
            $query->where('stores.region_id', $id);
        } elseif ($type === 'store') {
            $query->where('stores.id', $id);
        }

        return $query->count();
    }

    /**
     * Get total number of scheduled interviews for within a date range.
     *
     * @param string $type The type of view (e.g., national, division, area, store).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    protected function getTotalInterviewsScheduled(string $type = null, ?int $id = null, Carbon $startDate, Carbon $endDate): int
    {
        $query = DB::table('interviews')
            ->join('vacancies', 'interviews.vacancy_id', '=', 'interviews.id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->whereBetween('interviews.created_at', [$startDate, $endDate]);

        if ($type === 'division') {
            $query->where('stores.division_id', $id);
        } elseif ($type === 'region') {
            $query->where('stores.region_id', $id);
        } elseif ($type === 'store') {
            $query->where('stores.id', $id);
        }

        return $query->count();
    }

    /**
     * Get total number of completed interviews for within a date range.
     *
     * @param string $type The type of view (e.g., national, division, area, store).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    protected function getTotalInterviewsCompleted(string $type = null, ?int $id = null, Carbon $startDate, Carbon $endDate): int
    {
        $query = DB::table('interviews')
            ->join('vacancies', 'interviews.vacancy_id', '=', 'interviews.id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->whereNotNull('interviews.score')
            ->whereBetween('interviews.created_at', [$startDate, $endDate]);

        if ($type === 'division') {
            $query->where('stores.division_id', $id);
        } elseif ($type === 'region') {
            $query->where('stores.region_id', $id);
        } elseif ($type === 'store') {
            $query->where('stores.id', $id);
        }

        return $query->count();
    }

    /**
     * Get total number of appointed applicants for within a date range.
     *
     * @param string $type The type of view (e.g., national, division, area, store).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    protected function getTotalApplicantsAppointed(string $type = null, ?int $id = null, Carbon $startDate, Carbon $endDate): int
    {
        $query = DB::table('interviews')
            ->join('vacancies', 'interviews.vacancy_id', '=', 'interviews.id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->where('interviews.status', 'Appointed')
            ->whereBetween('interviews.created_at', [$startDate, $endDate]);

        if ($type === 'division') {
            $query->where('stores.division_id', $id);
        } elseif ($type === 'region') {
            $query->where('stores.region_id', $id);
        } elseif ($type === 'store') {
            $query->where('stores.id', $id);
        }

        return $query->count();
    }

    /**
     * Get total number of ompleted interviews for within a date range.
     *
     * @param string $type The type of view (e.g., national, division, area, store).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return int
     */
    protected function getTotalApplicantsRegretted(string $type = null, ?int $id = null, Carbon $startDate, Carbon $endDate): int
    {
        $query = DB::table('interviews')
            ->join('vacancies', 'interviews.vacancy_id', '=', 'interviews.id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->where('interviews.status', 'Regretted')
            ->whereBetween('interviews.created_at', [$startDate, $endDate]);

        if ($type === 'division') {
            $query->where('stores.division_id', $id);
        } elseif ($type === 'region') {
            $query->where('stores.region_id', $id);
        } elseif ($type === 'store') {
            $query->where('stores.id', $id);
        }

        return $query->count();
    }

    /**
     * Calculate the average time to shortlist for within a date range.
     *
     * @param string $type The type of view (e.g., national, division, area, store).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return string
     */
    protected function getAverageTimeToShortlist(string $type = null, ?int $id = null, Carbon $startDate, Carbon $endDate): string
    {
        // Retrieve all vacancies for the store within the specified date range
        $query = DB::table('vacancies')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')  // Join vacancies with stores
            ->whereBetween('vacancies.created_at', [$startDate, $endDate]);

        if ($type === 'division') {
            $query->where('stores.division_id', $id);
        } elseif ($type === 'region') {
            $query->where('stores.region_id', $id);
        } elseif ($type === 'store') {
            $query->where('stores.id', $id);
        }

        $vacancies = Vacancy::whereIn('id', $query->pluck('vacancies.id'))
            ->with('shortlists')
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
     * Calculate the average time to hire within a date range.
     *
     * @param string $type The type of view (e.g., national, division, area, store).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return string
     */
    protected function getAverageTimeToHire(string $type = null, ?int $id = null, Carbon $startDate, Carbon $endDate): string
    {
        $query = DB::table('vacancies')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')  // Join vacancies with stores
            ->whereBetween('vacancies.created_at', [$startDate, $endDate]);

        // Apply conditions based on the $type value
        if ($type === 'division') {
            $query->where('stores.division_id', $id);
        } elseif ($type === 'region') {
            $query->where('stores.region_id', $id);
        } elseif ($type === 'store') {
            $query->where('stores.id', $id);
        }

        // Get the results and load the 'appointed' relationship using Eloquent
        $vacancies = Vacancy::whereIn('id', $query->pluck('vacancies.id'))
            ->with(['appointed' => function ($query) {
                $query->orderBy('vacancy_fills.created_at', 'asc'); // Order by first appointment
            }])
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
}
