<?php

namespace App\Services\DataService\Reports;

use Carbon\Carbon;
use App\Models\Type;
use App\Models\Vacancy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate])->where('deleted', 'No');

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
        $vacancies = Vacancy::where('open_positions', 0)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('deleted', 'No');

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
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate])->where('deleted', 'No');

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

        // Group vacancies by month and calculate the total vacancies for each month
        $vacancies->get()->groupBy(function ($vacancy) {
            return $vacancy->created_at->format('M');
        })->each(function ($vacancies, $month) use (&$vacanciesByMonth) {
            $totalOpenPositions = $vacancies->sum(fn ($v) => $v->open_positions ?? 0);
            $totalFilledPositions = $vacancies->sum(fn ($v) => $v->filled_positions ?? 0);
            $vacanciesByMonth[$month] = $totalOpenPositions + $totalFilledPositions;
        });

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
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->where('deleted', 'No');

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
        $vacancies->get()->groupBy(function ($vacancy) {
            return $vacancy->updated_at->format('M');
        })->each(function ($vacancies, $month) use (&$filledVacanciesByMonth) {
            $filledVacanciesByMonth[$month] = $vacancies->sum(fn($v) => $v->filled_positions ?? 0);
        });

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
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate])->where('deleted', 'No');

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

        // Retrieve vacancies, group them by month and type, and calculate totals
        $vacancies->get()
        ->groupBy(function ($vacancy) {
            return $vacancy->created_at->format('M');
        })
        ->each(function ($vacancies, $month) use (&$vacanciesTypeByMonth) {
            $vacancies->groupBy(fn($vacancy) => $vacancy->type->name)
                ->each(function ($typeVacancies, $typeName) use ($month, &$vacanciesTypeByMonth) {
                    $totalOpenPositions = $typeVacancies->sum(fn($v) => $v->open_positions ?? 0);
                    $totalFilledPositions = $typeVacancies->sum(fn($v) => $v->filled_positions ?? 0);
                    $vacanciesTypeByMonth[$typeName][$month] = $totalOpenPositions + $totalFilledPositions;
                });
        });

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
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate])->where('deleted', 'No');

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

        // Retrieve vacancies, group them by type, and calculate totals
        $vacancies->get()->groupBy(fn($vacancy) => $vacancy->type->name)
            ->each(function ($vacancies, $typeName) use (&$vacanciesByType) {
                $totalOpenPositions = $vacancies->sum(fn($v) => $v->open_positions ?? 0);
                $totalFilledPositions = $vacancies->sum(fn($v) => $v->filled_positions ?? 0);
                $vacanciesByType[$typeName] = $totalOpenPositions + $totalFilledPositions;
            });

        return $vacanciesByType;
    }

    /**
     * Get the total number of filtered vacancies based on multiple criteria.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param array $filters An array of additional filters.
     * @return int The total count of filtered vacancies.
    */
    public function getTotalVacanciesFiltered(string $type, ?int $id, string $startDate, string $endDate, array $filters)
    {
        // Start building the query using the Vacancy model and filter by date range
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate])->where('deleted', 'No');

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

        // Apply all additional filters
        if (isset($filters['position_id'])) {
            $vacancies->where('position_id', $filters['position_id']);
        }
        if (isset($filters['open_positions'])) {
            $vacancies->where('open_positions', $filters['open_positions']);
        }
        if (isset($filters['filled_positions'])) {
            $vacancies->where('filled_positions', $filters['filled_positions']);
        }
        if (isset($filters['brand_id'])) {
            $vacancies->whereHas('store', function ($query) use ($filters) {
                $query->where('brand_id', $filters['brand_id']);
            });
        }
        if (isset($filters['division_id'])) {
            $vacancies->whereHas('store', function ($query) use ($filters) {
                $query->where('division_id', $filters['division_id']);
            });
        }
        if (isset($filters['region_id'])) {
            $vacancies->whereHas('store', function ($query) use ($filters) {
                $query->where('region_id', $filters['region_id']);
            });
        }
        if (isset($filters['store_id'])) {
            if (is_array($filters['store_id'])) {
                $vacancies->whereIn('store_id', $filters['store_id']);
            }
        }
        if (isset($filters['user_id'])) {
            $vacancies->where('user_id', $filters['user_id']);
        }
        if (isset($filters['type_id'])) {
            $vacancies->where('type_id', $filters['type_id']);
        }

        // Apply the `unactioned` filter
        if (isset($filters['unactioned'])) {
            if ($filters['unactioned'] === 'No') {
                // Get vacancies where shortlists exist and `applicant_ids` is not empty
                $vacancies->whereHas('shortlists', function ($query) {
                    $query->whereNotNull('applicant_ids')->where('applicant_ids', '!=', '[]');
                });
            } elseif ($filters['unactioned'] === 'Yes') {
                // Get vacancies with no shortlists or where shortlists exist but `applicant_ids` is null or empty
                $vacancies->whereDoesntHave('shortlists')
                    ->orWhereHas('shortlists', function ($query) {
                        $query->whereNull('applicant_ids')
                            ->orWhere('applicant_ids', '[]');
                    });
            }
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
     * Get the total number of filtered and filled vacancies based on multiple criteria.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param array $filters An array of additional filters.
     * @return int The total count of filtered vacancies.
    */
    public function getTotalVacanciesFilledFiltered(string $type, ?int $id, string $startDate, string $endDate, array $filters)
    {
        // Start building the query using the Vacancy model, filter for filled vacancies (open_positions = 0), and date range
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate])->where('deleted', 'No');

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

        // Apply all additional filters
        if (isset($filters['position_id'])) {
            $vacancies->where('position_id', $filters['position_id']);
        }
        if (isset($filters['brand_id'])) {
            $vacancies->whereHas('store', function ($query) use ($filters) {
                $query->where('brand_id', $filters['brand_id']);
            });
        }
        if (isset($filters['division_id'])) {
            $vacancies->whereHas('store', function ($query) use ($filters) {
                $query->where('division_id', $filters['division_id']);
            });
        }
        if (isset($filters['region_id'])) {
            $vacancies->whereHas('store', function ($query) use ($filters) {
                $query->where('region_id', $filters['region_id']);
            });
        }
        if (isset($filters['store_id'])) {
            if (is_array($filters['store_id'])) {
                $vacancies->whereIn('store_id', $filters['store_id']);
            }
        }
        if (isset($filters['user_id'])) {
            $vacancies->where('user_id', $filters['user_id']);
        }
        if (isset($filters['type_id'])) {
            $vacancies->where('type_id', $filters['type_id']);
        }

        // Apply the `unactioned` filter
        if (isset($filters['unactioned'])) {
            if ($filters['unactioned'] === 'No') {
                // Get vacancies where shortlists exist and `applicant_ids` is not empty
                $vacancies->whereHas('shortlists', function ($query) {
                    $query->whereNotNull('applicant_ids')->where('applicant_ids', '!=', '[]');
                });
            } elseif ($filters['unactioned'] === 'Yes') {
                // Get vacancies with no shortlists or where shortlists exist but `applicant_ids` is null or empty
                $vacancies->whereDoesntHave('shortlists')
                    ->orWhereHas('shortlists', function ($query) {
                        $query->whereNull('applicant_ids')
                            ->orWhere('applicant_ids', '[]');
                    });
            }
        }

        // Calculate the total sum of filled_positions, treating null as 0
        $totalFilledPositions = $vacancies->sum(DB::raw('COALESCE(filled_positions, 0)'));

        // Return the total filled positions
        return $totalFilledPositions;
    }

    /**
     * Get the total number of vacancies created by month within a given date range, filtered by additional criteria.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param array $filters An array of additional filters.
     * @return array An array of vacancy counts by month.
     */
    public function getTotalVacanciesByMonthFiltered(string $type, ?int $id, $startDate, $endDate, array $filters): array
    {
        // Initialize an array to hold the results, with months set to 0 from startDate to endDate
        $vacanciesByMonth = [];
        $currentDate = $startDate->copy()->startOfMonth(); // Start at the beginning of the month
        $currentEndDate = $endDate->copy();

        // Loop to populate the months between startDate and endDate (inclusive)
        while ($currentDate->lte($currentEndDate->endOfMonth())) { // Ensure we include all months up to the end of the month
            $monthName = $currentDate->format('M');
            $vacanciesByMonth[$monthName] = 0; // Initialize count for each month
            $currentDate->addMonth(); // Move to the next month
        }

        // Start building the query using the Vacancy model and filter by date range
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate])->where('deleted', 'No');

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

        // Apply additional filters if provided
        if (isset($filters['position_id'])) {
            $vacancies->where('position_id', $filters['position_id']);
        }
        if (isset($filters['open_positions'])) {
            $vacancies->where('open_positions', $filters['open_positions']);
        }
        if (isset($filters['filled_positions'])) {
            $vacancies->where('filled_positions', $filters['filled_positions']);
        }
        if (isset($filters['brand_id'])) {
            $vacancies->whereHas('store', function ($query) use ($filters) {
                $query->where('brand_id', $filters['brand_id']);
            });
        }
        if (isset($filters['division_id'])) {
            $vacancies->whereHas('store', function ($query) use ($filters) {
                $query->where('division_id', $filters['division_id']);
            });
        }
        if (isset($filters['region_id'])) {
            $vacancies->whereHas('store', function ($query) use ($filters) {
                $query->where('region_id', $filters['region_id']);
            });
        }
        if (isset($filters['store_id'])) {
            if (is_array($filters['store_id'])) {
                $vacancies->whereIn('store_id', $filters['store_id']);
            }
        }
        if (isset($filters['user_id'])) {
            $vacancies->where('user_id', $filters['user_id']);
        }
        if (isset($filters['type_id'])) {
            $vacancies->where('type_id', $filters['type_id']);
        }

        // Apply the `unactioned` filter
        if (isset($filters['unactioned'])) {
            if ($filters['unactioned'] === 'No') {
                $vacancies->whereHas('shortlists', function ($query) {
                    $query->whereNotNull('applicant_ids')->where('applicant_ids', '!=', '[]');
                });
            } elseif ($filters['unactioned'] === 'Yes') {
                $vacancies->whereDoesntHave('shortlists')
                    ->orWhereHas('shortlists', function ($query) {
                        $query->whereNull('applicant_ids')
                            ->orWhere('applicant_ids', '[]');
                    });
            }
        }

        // Group vacancies by month and calculate the total vacancies for each month
        $vacancies->get()->groupBy(function ($vacancy) {
            return $vacancy->created_at->format('M');
        })->each(function ($vacancies, $month) use (&$vacanciesByMonth) {
            $totalOpenPositions = $vacancies->sum(fn ($v) => $v->open_positions ?? 0);
            $totalFilledPositions = $vacancies->sum(fn ($v) => $v->filled_positions ?? 0);
            $vacanciesByMonth[$month] = $totalOpenPositions + $totalFilledPositions;
        });

        return $vacanciesByMonth;
    }

    /**
     * Get the total number of vacancies by type within a given date range, with additional filters.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param array $filters An array of additional filters.
     * @return array An array with vacancy counts by type.
     */
    public function getTotalVacanciesByTypeFiltered(string $type, ?int $id, $startDate, $endDate, array $filters): array
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
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate])->where('deleted', 'No');

        // Apply type filtering based on store, division, or region
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

        // Apply additional filters
        if (isset($filters['position_id'])) {
            $vacancies->where('position_id', $filters['position_id']);
        }
        if (isset($filters['open_positions'])) {
            $vacancies->where('open_positions', $filters['open_positions']);
        }
        if (isset($filters['filled_positions'])) {
            $vacancies->where('filled_positions', $filters['filled_positions']);
        }
        if (isset($filters['brand_id'])) {
            $vacancies->whereHas('store', function ($query) use ($filters) {
                $query->where('brand_id', $filters['brand_id']);
            });
        }
        if (isset($filters['division_id'])) {
            $vacancies->whereHas('store', function ($query) use ($filters) {
                $query->where('division_id', $filters['division_id']);
            });
        }
        if (isset($filters['region_id'])) {
            $vacancies->whereHas('store', function ($query) use ($filters) {
                $query->where('region_id', $filters['region_id']);
            });
        }
        if (isset($filters['store_id'])) {
            if (is_array($filters['store_id'])) {
                $vacancies->whereIn('store_id', $filters['store_id']);
            }
        }
        if (isset($filters['user_id'])) {
            $vacancies->where('user_id', $filters['user_id']);
        }
        if (isset($filters['type_id'])) {
            $vacancies->where('type_id', $filters['type_id']);
        }

        // Apply the `unactioned` filter
        if (isset($filters['unactioned'])) {
            if ($filters['unactioned'] === 'No') {
                // Get vacancies where shortlists exist and `applicant_ids` is not empty
                $vacancies->whereHas('shortlists', function ($query) {
                    $query->whereNotNull('applicant_ids')->where('applicant_ids', '!=', '[]');
                });
            } elseif ($filters['unactioned'] === 'Yes') {
                // Get vacancies with no shortlists or where shortlists exist but `applicant_ids` is null or empty
                $vacancies->whereDoesntHave('shortlists')
                    ->orWhereHas('shortlists', function ($query) {
                        $query->whereNull('applicant_ids')
                            ->orWhere('applicant_ids', '[]');
                    });
            }
        }

        // Retrieve vacancies, group them by type, and calculate totals
        $vacancies->get()->groupBy(fn($vacancy) => $vacancy->type->name)
            ->each(function ($vacancies, $typeName) use (&$vacanciesByType) {
                $totalOpenPositions = $vacancies->sum(fn($v) => $v->open_positions ?? 0);
                $totalFilledPositions = $vacancies->sum(fn($v) => $v->filled_positions ?? 0);
                $vacanciesByType[$typeName] = $totalOpenPositions + $totalFilledPositions;
            });

        return $vacanciesByType;
    }
}
