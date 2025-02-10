<?php

namespace App\Services\DataService;

use Carbon\Carbon;
use App\Models\Store;
use App\Models\Vacancy;
use App\Models\Interview;
use Illuminate\Support\Facades\DB;

class VacancyDataService
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

        // Calculate the sum of open_positions and filled_positions, treating null as 0
        $totalOpenPositions = $vacancies->sum(DB::raw('COALESCE(open_positions, 0)'));
        $totalFilledPositions = $vacancies->sum(DB::raw('COALESCE(filled_positions, 0)'));

        // Return the combined total of open_positions and filled_positions
        $totalVacancies = $totalOpenPositions + $totalFilledPositions;

        // Return the total count of vacancies
        return $totalVacancies;
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

        // Calculate the total sum of filled_positions, treating null as 0
        $totalFilledPositions = $vacancies->sum(DB::raw('COALESCE(filled_positions, 0)'));

        // Return the total filled positions
        return $totalFilledPositions;
    }

    /**
     * Get Total Interviews Scheduled for a specific type within a date range.
     *
     * @param string $type The type of view (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering interviews.
     * @param \Carbon\Carbon $endDate The end date for filtering interviews.
     * @return int The total count of scheduled interviews.
     */
    public function getTotalInterviewsScheduled(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Start building the query using the Interview model and filter by date range
        $interviews = Interview::whereBetween('created_at', [$startDate, $endDate]);

        // Prioritize filtering by store, followed by division, then region using Eloquent relationships
        if ($type === 'store') {
            $interviews->whereHas('vacancy', function ($query) use ($id) {
                $query->where('store_id', $id);  // Directly filter by store_id in vacancies table
            });
        } elseif ($type === 'division') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('division_id', $id);  // Filter by division_id in stores table
            });
        } elseif ($type === 'region') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('region_id', $id);  // Filter by region_id in stores table
            });
        }

        // Return the total count of scheduled interviews
        return $interviews->count();
    }

    /**
     * Get total number of completed interviews for a specific type within a date range.
     *
     * @param string $type The type of view (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering interviews.
     * @param \Carbon\Carbon $endDate The end date for filtering interviews.
     * @return int The total count of completed interviews.
     */
    public function getTotalInterviewsCompleted(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Start building the query using the Interview model, filter for completed interviews (score is not null) and date range
        $interviews = Interview::whereNotNull('score')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Prioritize filtering by store, followed by division, then region using Eloquent relationships
        if ($type === 'store') {
            $interviews->whereHas('vacancy', function ($query) use ($id) {
                $query->where('store_id', $id);  // Directly filter by store_id in vacancies table
            });
        } elseif ($type === 'division') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('division_id', $id);  // Filter by division_id in stores table
            });
        } elseif ($type === 'region') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('region_id', $id);  // Filter by region_id in stores table
            });
        }

        // Return the total count of completed interviews
        return $interviews->count();
    }

    /**
     * Get Total Applicants Appointedfor a specific type within a date range.
     *
     * @param string $type The type of view (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering interviews.
     * @param \Carbon\Carbon $endDate The end date for filtering interviews.
     * @return int The total count of appointed applicants.
     */
    public function getTotalApplicantsAppointed(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Start building the query to retrieve vacancies based on type (store, division, region)
        $vacancies = Vacancy::when($type === 'store', function ($query) use ($id) {
            return $query->where('store_id', $id);
        })
        ->when($type === 'division', function ($query) use ($id) {
            return $query->whereHas('store', function ($q) use ($id) {
                $q->where('division_id', $id);
            });
        })
        ->when($type === 'region', function ($query) use ($id) {
            return $query->whereHas('store', function ($q) use ($id) {
                $q->where('region_id', $id);
            });
        })
        ->with(['appointed' => function ($query) use ($startDate, $endDate) {
            // Only fetch appointed applicants within the date range
            $query->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]);
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
     * Get total number of regretted applicants for a specific type within a date range.
     *
     * @param string $type The type of view (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering interviews.
     * @param \Carbon\Carbon $endDate The end date for filtering interviews.
     * @return int The total count of regretted applicants.
     */
    public function getTotalApplicantsRegretted(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Start building the query using the Interview model, filter for regretted applicants and date range
        $interviews = Interview::where('status', 'Regretted')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Prioritize filtering by store, followed by division, then region using Eloquent relationships
        if ($type === 'store') {
            $interviews->whereHas('vacancy', function ($query) use ($id) {
                $query->where('store_id', $id);  // Correctly filter by store_id through the vacancy relationship
            });
        } elseif ($type === 'division') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('division_id', $id);  // Filter by division_id in stores table
            });
        } elseif ($type === 'region') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('region_id', $id);  // Filter by region_id in stores table
            });
        }

        // Return the total count of regretted applicants
        return $interviews->count();
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
    public function getAverageTimeToShortlist(string $type = null, ?int $id = null, Carbon $startDate, Carbon $endDate): string
    {
        // Retrieve vacancies within the specified date range using Eloquent and filter by store, division, or region
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate])
            ->when($type === 'store', function ($query) use ($id) {
                return $query->where('store_id', $id);
            })
            ->when($type === 'division', function ($query) use ($id) {
                return $query->whereHas('store', function ($q) use ($id) {
                    $q->where('division_id', $id);
                });
            })
            ->when($type === 'region', function ($query) use ($id) {
                return $query->whereHas('store', function ($q) use ($id) {
                    $q->where('region_id', $id);
                });
            })
            ->with('shortlists')
            ->get();

        $totalTimeInSeconds = 0;
        $shortlistCount = 0;

        // Loop through each vacancy and its associated shortlists
        foreach ($vacancies as $vacancy) {
            foreach ($vacancy->shortlists as $shortlist) {
                // Calculate the time difference between vacancy creation and shortlist creation in seconds
                $totalTimeInSeconds += $vacancy->created_at->diffInSeconds($shortlist->created_at);
                $shortlistCount++;
            }
        }

        // Calculate the average time to shortlist
        if ($shortlistCount > 0) {
            $averageTimeInSeconds = $totalTimeInSeconds / $shortlistCount;

            // Convert total seconds into days, hours, minutes, and seconds
            $totalDays = floor($averageTimeInSeconds / (24 * 3600));
            $remainingSeconds = $averageTimeInSeconds % (24 * 3600);

            $totalHours = floor($remainingSeconds / 3600);
            $remainingSeconds %= 3600;

            $totalMinutes = floor($remainingSeconds / 60);
            $totalSeconds = $remainingSeconds % 60;

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
        }

        // Return default format if no shortlists
        return '0D 0H 0M';
    }

    /**
     * Calculate the average time to hire for a specific type within a date range.
     *
     * @param string $type The type of view (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering vacancies.
     * @param \Carbon\Carbon $endDate The end date for filtering vacancies.
     * @return string The average time to hire in the format 'D H M' (Days, Hours, Minutes).
     */
    public function getAverageTimeToHire(string $type = null, ?int $id = null, Carbon $startDate, Carbon $endDate): string
    {
        // Retrieve vacancies within the specified date range using Eloquent and filter by store, division, or region
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate])
            ->when($type === 'store', function ($query) use ($id) {
                return $query->where('store_id', $id);
            })
            ->when($type === 'division', function ($query) use ($id) {
                return $query->whereHas('store', function ($q) use ($id) {
                    $q->where('division_id', $id);
                });
            })
            ->when($type === 'region', function ($query) use ($id) {
                return $query->whereHas('store', function ($q) use ($id) {
                    $q->where('region_id', $id);
                });
            })
            ->with(['appointed' => function ($query) {
                $query->orderBy('vacancy_fills.created_at', 'asc'); // Order by first appointment
            }])
            ->get();

        $totalTimeInSeconds = 0;
        $hiringCount = 0;

        // Loop through each vacancy and calculate the time difference for the first appointed record
        foreach ($vacancies as $vacancy) {
            if ($vacancy->appointed->isNotEmpty()) {
                // Get the first appointment record
                $firstAppointed = $vacancy->appointed->first();

                // Calculate the time difference between vacancy creation and the first appointment in seconds
                $totalTimeInSeconds += $vacancy->created_at->diffInSeconds($firstAppointed->pivot->created_at);
                $hiringCount++;
            }
        }

        // Calculate the average time to hire
        if ($hiringCount > 0) {
            $averageTimeInSeconds = $totalTimeInSeconds / $hiringCount;

            // Convert total seconds into days, hours, minutes, and seconds
            $totalDays = floor($averageTimeInSeconds / (24 * 3600));
            $remainingSeconds = $averageTimeInSeconds % (24 * 3600);

            $totalHours = floor($remainingSeconds / 3600);
            $remainingSeconds %= 3600;

            $totalMinutes = floor($remainingSeconds / 60);
            $totalSeconds = $remainingSeconds % 60;

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
        }

        // Return default format if no appointments
        return '0D 0H 0M';
    }

    /**
     * Get the Applicants Appointed within a date range for vacancies filtered by store, division, or region.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering appointments.
     * @param \Carbon\Carbon $endDate The end date for filtering appointments.
     * @return int The total number of appointed applicants.
     */
    public function getApplicantsAppointed(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Start building the query to retrieve vacancies based on type (store, division, region)
        $vacancies = Vacancy::when($type === 'store', function ($query) use ($id) {
                return $query->where('store_id', $id);
        })
        ->when($type === 'division', function ($query) use ($id) {
            return $query->whereHas('store', function ($q) use ($id) {
                $q->where('division_id', $id);
            });
        })
        ->when($type === 'region', function ($query) use ($id) {
            return $query->whereHas('store', function ($q) use ($id) {
                $q->where('region_id', $id);
            });
        })
        ->with(['appointed' => function ($query) use ($startDate, $endDate) {
            // Only fetch appointed applicants within the date range
            $query->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]);
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
     * Get the Applicants Appointed By Month for vacancies filtered by store, division, or region.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering appointments.
     * @param \Carbon\Carbon $endDate The end date for filtering appointments.
     * @return array An array of appointed applicants grouped by month and year.
     */
    public function getApplicantsAppointedByMonth(string $type, ?int $id, $startDate, $endDate): array
    {
        // Initialize an array to hold the results, with months and years set to 0 from startDate to endDate
        $applicantsByMonth = [];
        $currentDate = $startDate->copy();

        // Loop to populate months and years from startDate to endDate
        while ($currentDate->lte($endDate)) {
            $monthYear = $currentDate->format("M'y"); // Format as Jan'24
            $applicantsByMonth[$monthYear] = 0;
            $currentDate->addMonth();
        }

        // Ensure the last month is included
        $lastMonthYear = $endDate->format("M'y");
        if (!array_key_exists($lastMonthYear, $applicantsByMonth)) {
            $applicantsByMonth[$lastMonthYear] = 0;
        }

        // Retrieve vacancies based on the type (store, division, or region) and within the date range
        $vacancies = Vacancy::when($type === 'store', function ($query) use ($id) {
                return $query->where('store_id', $id);
        })
            ->when($type === 'division', function ($query) use ($id) {
                return $query->whereHas('store', function ($q) use ($id) {
                    $q->where('division_id', $id);
                });
            })
            ->when($type === 'region', function ($query) use ($id) {
                return $query->whereHas('store', function ($q) use ($id) {
                    $q->where('region_id', $id);
                });
            })
            ->with(['appointed' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]); // Only fetch appointed within the date range
            }])
            ->get();

        // Group appointed applicants by the month and year of their appointment date and count them
        foreach ($vacancies as $vacancy) {
            foreach ($vacancy->appointed as $applicant) {
                // Get the month and year from the created_at date in the vacancy_fills table (the appointment date)
                $monthYear = $applicant->pivot->created_at->format("M'y"); // Format as Jan'24
                // Increment the count for the corresponding month and year
                if (isset($applicantsByMonth[$monthYear])) {
                    $applicantsByMonth[$monthYear]++;
                }
            }
        }

        return $applicantsByMonth;
    }

    /**
     * Get the total number of stores using the solution within a given date range.
     *
     * A store is considered to be using the solution if it has at least one vacancy
     * with at least one appointed applicant, and the vacancy was created within the specified date range.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter (null for 'all').
     * @param \Carbon\Carbon $startDate The start date for filtering vacancies.
     * @param \Carbon\Carbon $endDate The end date for filtering vacancies.
     * @return int The total count of stores using the solution.
     */
    public function getTotalStoresUsingSolution(string $type, ?int $id, $startDate, $endDate): int
    {
        // Retrieve stores that have at least one vacancy with at least one appointed applicant
        // and the vacancy was created within the specified date range
        $totalStores = Store::whereHas('vacancies', function ($vacancyQuery) use ($startDate, $endDate) {
            // Filter vacancies based on the created_at date range and the presence of appointed applicants
            $vacancyQuery->whereBetween('created_at', [$startDate, $endDate])
                         ->whereHas('appointed');
        })->count();

        // Return the count of stores using the solution
        return $totalStores;
    }

    /**
     * Get the total number of stores.
     *
     * @return int The total count of stores.
     */
    public function getTotalStores(string $type, ?int $id, $startDate, $endDate): int
    {
        // Count and return the total number of stores
        return Store::count();
    }
}
