<?php

namespace App\Services\DataService;

use App\Models\ApplicantMonthlyData;
use App\Models\ApplicantTotalData;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
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
