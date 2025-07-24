<?php

namespace App\Services\DataService\Reports;

use Carbon\Carbon;
use App\Models\Town;
use App\Models\Race;
use App\Models\State;
use App\Models\Store;
use App\Models\Gender;
use App\Models\Vacancy;
use App\Models\Province;
use App\Models\Applicant;
use App\Models\VacancyFill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplicantsReportDataService
{
    /**
     * Get the total number of applicants within a given date range, regardless of their state.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @return int The total count of applicants.
     */
    public function getTotalApplicants(string $type = 'all', ?int $id = null, Carbon $startDate, Carbon $endDate, float $maxDistanceFromStore = 50): int
    {
        // Retrieve the complete state ID
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return 0; // Handle case where 'complete' state does not exist
        }

        // Base query to filter applicants within the date range and with the appropriate state
        $query = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '>=', $completeStateID);

        // If the type is 'all', return the total count directly
        if ($type === 'all') {
            return $query->count();
        }

        // Get stores based on the type (store, division, or region)
        $stores = Store::when($type === 'store', function ($query) use ($id) {
            return $query->where('id', $id);
        })
        ->when($type === 'division', function ($query) use ($id) {
            return $query->where('division_id', $id);
        })
        ->when($type === 'region', function ($query) use ($id) {
            return $query->where('region_id', $id);
        })
        ->get();

        // If no stores are found, return 0
        if ($stores->isEmpty()) {
            return 0;
        }

        // Loop through each store and filter applicants by proximity
        $totalApplicants = 0;
        foreach ($stores as $store) {
            if ($store->coordinates) {
                // Extract latitude and longitude from store coordinates
                $storeCoordinates = explode(',', $store->coordinates);
                $storeLat = floatval($storeCoordinates[0]);
                $storeLng = floatval($storeCoordinates[1]);

                // Add applicants within the distance range using MySQL ST_Distance_Sphere
                $applicantsCount = Applicant::whereBetween('created_at', [$startDate, $endDate])
                    ->where('state_id', '>=', $completeStateID)
                    ->whereRaw("
                        ST_Distance_Sphere(
                            point(
                                SUBSTRING_INDEX(applicants.coordinates, ',', -1), 
                                SUBSTRING_INDEX(applicants.coordinates, ',', 1)
                            ), 
                            point(?, ?)
                        ) <= ?
                    ", [$storeLng, $storeLat, $maxDistanceFromStore * 1000]) // Convert km to meters
                    ->count();

                // Add the count from this store to the total applicants count
                $totalApplicants += $applicantsCount;
            }
        }

        return $totalApplicants;
    }

    /**
     * Get the total number of appointed applicants.
     *
     * Appointed applicants are defined as applicants with a non-null `appointed_id`.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter (null for 'all').
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @return int The total count of appointed applicants.
     */
    public function getTotalAppointedApplicants(string $type = 'all', ?int $id = null, Carbon $startDate, Carbon $endDate): int
    {
        // Query the VacancyFill model and filter by created_at within the date range
        $query = VacancyFill::whereBetween('created_at', [$startDate, $endDate]);

        // Apply additional filtering based on the type
        if ($type === 'store') {
            $query->whereHas('vacancy', function ($vacancyQuery) use ($id) {
                $vacancyQuery->where('store_id', $id);
            });
        } elseif ($type === 'division') {
            $query->whereHas('vacancy.store', function ($storeQuery) use ($id) {
                $storeQuery->where('division_id', $id);
            });
        } elseif ($type === 'region') {
            $query->whereHas('vacancy.store', function ($storeQuery) use ($id) {
                $storeQuery->where('region_id', $id);
            });
        }

        // Return the count of appointed applicants
        return $query->count();
    }

    /**
     * Get the number of talent pool applicants by month within a given distance from the store, division, or region, or all applicants if type is 'all'.
     *
     * @param string|null $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param float $maxDistanceFromStore The maximum distance from the store in kilometers.
     * @return array An array of applicants by month.
     */
    public function getTotalApplicantsByMonth(string $type = 'all', ?int $id = null, $startDate, $endDate, float $maxDistanceFromStore = 50): array
    {
        // Initialize result array with zeros for all months between startDate and endDate
        $applicantsByMonth = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $monthName = $currentDate->format('M');
            $applicantsByMonth[$monthName] = 0;
            $currentDate->addMonth();
        }

        // Get the complete state ID
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return $applicantsByMonth;
        }

        // Base query for applicants
        $query = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '>=', $completeStateID);

        // Apply distance filter if type is not 'all'
        if ($type !== 'all') {
            $storeIds = Store::when($type === 'store', function ($query) use ($id) {
                    return $query->where('id', $id);
            })
                ->when($type === 'division', function ($query) use ($id) {
                    return $query->where('division_id', $id);
                })
                ->when($type === 'region', function ($query) use ($id) {
                    return $query->where('region_id', $id);
                })
                ->pluck('id');

            if ($storeIds->isEmpty()) {
                return $applicantsByMonth;
            }

            $query->whereExists(function ($subQuery) use ($storeIds, $maxDistanceFromStore) {
                $subQuery->select(DB::raw(1))
                    ->from('stores')
                    ->whereIn('stores.id', $storeIds)
                    ->whereRaw("ST_Distance_Sphere(
                        point(SUBSTRING_INDEX(applicants.coordinates, ',', -1), SUBSTRING_INDEX(applicants.coordinates, ',', 1)),
                        point(SUBSTRING_INDEX(stores.coordinates, ',', -1), SUBSTRING_INDEX(stores.coordinates, ',', 1))
                    ) <= ?", [$maxDistanceFromStore * 1000]);
            });
        }

        // Aggregate counts by month
        $results = $query->selectRaw('DATE_FORMAT(created_at, "%b") as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Merge results into the initialized array
        foreach ($results as $month => $count) {
            $applicantsByMonth[$month] = $count;
        }

        return $applicantsByMonth;
    }

    /**
     * Get the Applicants Appointed By Month for vacancies filtered by store, division, or region.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering appointments.
     * @param \Carbon\Carbon $endDate The end date for filtering appointments.
     * @return array An array of appointed applicants grouped by month.
     */
    public function getTotalApplicantsAppointedByMonth(string $type = 'all', ?int $id, $startDate, $endDate): array
    {
        // Initialize result array with zeros for all months
        $applicantsByMonth = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $monthName = $currentDate->format('M');
            $applicantsByMonth[$monthName] = 0;
            $currentDate->addMonth();
        }

        // Build query with joins to filter vacancies and count appointments
        $query = DB::table('vacancy_fills')
            ->join('vacancies', 'vacancy_fills.vacancy_id', '=', 'vacancies.id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]);

        // Apply type-specific filters
        $query->when($type === 'store', function ($q) use ($id) {
                return $q->where('stores.id', $id);
        })
            ->when($type === 'division', function ($q) use ($id) {
                return $q->where('stores.division_id', $id);
            })
            ->when($type === 'region', function ($q) use ($id) {
                return $q->where('stores.region_id', $id);
            });

        // Aggregate counts by month
        $results = $query->selectRaw('DATE_FORMAT(vacancy_fills.created_at, "%b") as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Merge results
        foreach ($results as $month => $count) {
            $applicantsByMonth[$month] = $count;
        }

        return $applicantsByMonth;
    }

    /**
     * Get the number of talent pool applicants by month and gender within a given distance from the store, division, or region,
     * or all applicants if type is 'all'.
     *
     * @param string|null $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param float $maxDistanceFromStore The maximum distance from the store in kilometers.
     * @return array An array of applicants by month and gender.
     */
    public function getTotalApplicantsGenderByMonth(string $type = 'all', ?int $id = null, $startDate, $endDate, float $maxDistanceFromStore = 50): array
    {
        // Initialize result array with zeros for all genders and months
        $applicantsGenderByMonth = [];
        $genders = Gender::all()->pluck('name')->toArray();
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $monthName = $currentDate->format('M');
            foreach ($genders as $gender) {
                $applicantsGenderByMonth[$gender][$monthName] = 0;
            }
            $currentDate->addMonth();
        }

        // Get complete state ID
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return $applicantsGenderByMonth;
        }

        // Build query with gender join
        $query = Applicant::join('genders', 'applicants.gender_id', '=', 'genders.id')
            ->whereBetween('applicants.created_at', [$startDate, $endDate])
            ->where('applicants.state_id', '>=', $completeStateID)
            ->whereNotNull('applicants.gender_id');

        // Apply distance filter if type is not 'all'
        if ($type !== 'all') {
            $storeIds = Store::when($type === 'store', function ($q) use ($id) {
                    return $q->where('id', $id);
            })
                ->when($type === 'division', function ($q) use ($id) {
                    return $q->where('division_id', $id);
                })
                ->when($type === 'region', function ($q) use ($id) {
                    return $q->where('region_id', $id);
                })
                ->pluck('id');

            if ($storeIds->isEmpty()) {
                return $applicantsGenderByMonth;
            }

            $query->whereExists(function ($subQuery) use ($storeIds, $maxDistanceFromStore) {
                $subQuery->select(DB::raw(1))
                    ->from('stores')
                    ->whereIn('stores.id', $storeIds)
                    ->whereRaw("ST_Distance_Sphere(
                        point(SUBSTRING_INDEX(applicants.coordinates, ',', -1), SUBSTRING_INDEX(applicants.coordinates, ',', 1)),
                        point(SUBSTRING_INDEX(stores.coordinates, ',', -1), SUBSTRING_INDEX(stores.coordinates, ',', 1))
                    ) <= ?", [$maxDistanceFromStore * 1000]);
            });
        }

        // Aggregate by gender and month
        $results = $query->selectRaw('genders.name as gender, DATE_FORMAT(applicants.created_at, "%b") as month, COUNT(*) as count')
            ->groupBy('gender', 'month')
            ->get();

        // Populate results
        foreach ($results as $result) {
            $applicantsGenderByMonth[$result->gender][$result->month] = $result->count;
        }

        return $applicantsGenderByMonth;
    }

    /**
     * Get the number of talent pool applicants by month and race within a given distance from the store, division, or region,
     * or all applicants if type is 'all'.
     *
     * @param string|null $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param float $maxDistanceFromStore The maximum distance from the store in kilometers.
     * @return array An array of applicants by month and race.
     */
    public function getTotalApplicantsRaceByMonth(string $type = 'all', ?int $id = null, $startDate, $endDate, float $maxDistanceFromStore = 50): array
    {
        // Initialize result array with zeros for all races and months
        $applicantsRaceByMonth = [];
        $races = Race::all()->pluck('name')->toArray();
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $monthName = $currentDate->format('M');
            foreach ($races as $race) {
                $applicantsRaceByMonth[$race][$monthName] = 0;
            }
            $currentDate->addMonth();
        }

        // Get complete state ID
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return $applicantsRaceByMonth;
        }

        // Build query with race join
        $query = Applicant::join('races', 'applicants.race_id', '=', 'races.id')
            ->whereBetween('applicants.created_at', [$startDate, $endDate])
            ->where('applicants.state_id', '>=', $completeStateID)
            ->whereNotNull('applicants.race_id');

        // Apply distance filter if type is not 'all'
        if ($type !== 'all') {
            $storeIds = Store::when($type === 'store', function ($q) use ($id) {
                    return $q->where('id', $id);
            })
                ->when($type === 'division', function ($q) use ($id) {
                    return $q->where('division_id', $id);
                })
                ->when($type === 'region', function ($q) use ($id) {
                    return $q->where('region_id', $id);
                })
                ->pluck('id');

            if ($storeIds->isEmpty()) {
                return $applicantsRaceByMonth;
            }

            $query->whereExists(function ($subQuery) use ($storeIds, $maxDistanceFromStore) {
                $subQuery->select(DB::raw(1))
                    ->from('stores')
                    ->whereIn('stores.id', $storeIds)
                    ->whereRaw("ST_Distance_Sphere(
                        point(SUBSTRING_INDEX(applicants.coordinates, ',', -1), SUBSTRING_INDEX(applicants.coordinates, ',', 1)),
                        point(SUBSTRING_INDEX(stores.coordinates, ',', -1), SUBSTRING_INDEX(stores.coordinates, ',', 1))
                    ) <= ?", [$maxDistanceFromStore * 1000]);
            });
        }

        // Aggregate by race and month
        $results = $query->selectRaw('races.name as race, DATE_FORMAT(applicants.created_at, "%b") as month, COUNT(*) as count')
            ->groupBy('race', 'month')
            ->get();

        // Populate results
        foreach ($results as $result) {
            $applicantsRaceByMonth[$result->race][$result->month] = $result->count;
        }

        return $applicantsRaceByMonth;
    }

    /**
     * Get the total number of filtered applicants based on multiple criteria.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param array $filters An array of additional filters.
     * @param float $maxDistanceFromStore The maximum distance from the store in kilometers.
     * @return int The total count of filtered applicants.
     */
    public function getTotalApplicantsFiltered(string $type = 'all', ?int $id = null, Carbon $startDate, Carbon $endDate, array $filters, float $maxDistanceFromStore = 50): int
    {
        // Retrieve the complete state ID
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return 0;
        }

        // Base query for applicants
        $query = Applicant::query();

        // Apply all additional filters
        if (isset($filters['gender_id'])) {
            $query->where('gender_id', $filters['gender_id']);
        }
        if (isset($filters['race_id'])) {
            $query->where('race_id', $filters['race_id']);
        }
        if (isset($filters['education_id'])) {
            $query->where('education_id', $filters['education_id']);
        }
        if (isset($filters['experience_id'])) {
            $query->where('experience_id', $filters['experience_id']);
        }
        if (isset($filters['employment'])) {
            $query->where('employment', $filters['employment']);
        }

        // Age, literacy, numeracy, situational, overall filters
        if (isset($filters['min_age']) && isset($filters['max_age'])) {
            $query->whereBetween('age', [$filters['min_age'], $filters['max_age']]);
        }
        if (isset($filters['min_literacy']) && isset($filters['max_literacy'])) {
            $query->whereBetween('literacy_score', [$filters['min_literacy'], $filters['max_literacy']]);
        }
        if (isset($filters['min_numeracy']) && isset($filters['max_numeracy'])) {
            $query->whereBetween('numeracy_score', [$filters['min_numeracy'], $filters['max_numeracy']]);
        }
        if (isset($filters['min_situational']) && isset($filters['max_situational'])) {
            $query->whereBetween('situational_score', [$filters['min_situational'], $filters['max_situational']]);
        }
        if (isset($filters['min_overall']) && isset($filters['max_overall'])) {
            $query->whereBetween('overall_score', [$filters['min_overall'], $filters['max_overall']]);
        }

        // Completed filter
        if (isset($filters['completed'])) {
            if ($filters['completed'] === 'Yes') {
                $query->where('state_id', '>=', $completeStateID);
            } elseif ($filters['completed'] === 'No') {
                $query->where('state_id', '<', $completeStateID);
            }
        } else {
            $query->where('state_id', '>=', $completeStateID);
        }

        // Shortlisted filter
        if (isset($filters['shortlisted'])) {
            if ($filters['shortlisted'] === 'Yes') {
                $query->whereNotNull('shortlist_id');

                // Apply geographic filters for shortlisted applicants
                if (isset($filters['division_id'])) {
                    $query->whereHas('shortlist.vacancy.store', function ($storeQuery) use ($filters) {
                        $storeQuery->where('division_id', $filters['division_id']);
                    });
                } elseif (isset($filters['region_id'])) {
                    $query->whereHas('shortlist.vacancy.store', function ($storeQuery) use ($filters) {
                        $storeQuery->where('region_id', $filters['region_id']);
                    });
                } elseif (isset($filters['store_id'])) {
                    $query->whereHas('shortlist.vacancy', function ($vacancyQuery) use ($filters) {
                        if (is_array($filters['store_id'])) {
                            $vacancyQuery->whereIn('store_id', $filters['store_id']);
                        }
                    });
                }
            } elseif ($filters['shortlisted'] === 'No') {
                $query->whereNull('shortlist_id');
            }
        }

        // Interviewed filter
        if (isset($filters['interviewed'])) {
            if ($filters['interviewed'] === 'Yes') {
                $query->whereHas('interviews', function ($interviewQuery) {
                    $interviewQuery->whereNotNull('score');
                });
            } elseif ($filters['interviewed'] === 'No') {
                $query->where(function ($q) {
                    $q->doesntHave('interviews')
                    ->orWhereHas('interviews', function ($interviewQuery) {
                        $interviewQuery->whereNull('score');
                    });
                });
            }

            if (isset($filters['division_id'])) {
                $query->whereHas('interviews.vacancy.store', function ($storeQuery) use ($filters) {
                    $storeQuery->where('division_id', $filters['division_id']);
                });
            } elseif (isset($filters['region_id'])) {
                $query->whereHas('interviews.vacancy.store', function ($storeQuery) use ($filters) {
                    $storeQuery->where('region_id', $filters['region_id']);
                });
            } elseif (isset($filters['store_id'])) {
                $query->whereHas('interviews.vacancy', function ($vacancyQuery) use ($filters) {
                    if (is_array($filters['store_id'])) {
                        $vacancyQuery->whereIn('store_id', $filters['store_id']);
                    }
                });
            }
        }

        // Appointed filter
        if (isset($filters['appointed']) && $filters['appointed'] === 'Yes') {
            $query->whereHas('vacancyFills', function ($vacancyQuery) use ($filters, $startDate, $endDate) {
                    // Apply date range to `vacancy_fills.created_at`
                    $vacancyQuery->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]);

                    // Apply geographic filtering based on division, region, or store
                if (isset($filters['division_id'])) {
                    $vacancyQuery->whereHas('store', function ($storeQuery) use ($filters) {
                        $storeQuery->where('division_id', $filters['division_id']);
                    });
                } elseif (isset($filters['region_id'])) {
                    $vacancyQuery->whereHas('store', function ($storeQuery) use ($filters) {
                        $storeQuery->where('region_id', $filters['region_id']);
                    });
                } elseif (isset($filters['store_id'])) {
                    if (is_array($filters['store_id'])) {
                        $vacancyQuery->whereIn('store_id', $filters['store_id']);
                    }
                }
            });
        } else {
            // Default date range filter for applicants
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalApplicants = 0;

        // Only apply geographic filtering if `shortlisted`, `interviewed`, or `appointed` do not explicitly exclude applicants
        if ((!isset($filters['shortlisted']) || $filters['shortlisted'] == 'No') && (!isset($filters['interviewed']) || $filters['interviewed'] == 'No') && (!isset($filters['appointed']) || $filters['appointed'] == 'No') && (isset($filters['store_id']) || isset($filters['region_id']) || isset($filters['division_id']))) {
            // Get stores based on the filter priority: division -> region -> store
            $stores = Store::when(isset($filters['division_id']), function ($query) use ($filters) {
                    return $query->where('division_id', $filters['division_id']);
            })
                ->when(isset($filters['region_id']), function ($query) use ($filters) {
                    return $query->where('region_id', $filters['region_id']);
                })
                ->when(isset($filters['store_id']), function ($query) use ($filters) {
                    if (is_array($filters['store_id'])) {
                        return $query->whereIn('id', $filters['store_id']);
                    }
                })
                ->get();

            foreach ($stores as $store) {
                if ($store->coordinates) {
                    [$storeLat, $storeLng] = array_map('floatval', explode(',', $store->coordinates));
                    $storeQuery = clone $query;
                    $storeQuery->whereRaw("ST_Distance_Sphere(
                        point(SUBSTRING_INDEX(applicants.coordinates, ',', -1), SUBSTRING_INDEX(applicants.coordinates, ',', 1)), 
                        point(?, ?)) <= ?", [$storeLng, $storeLat, $maxDistanceFromStore * 1000]);
                    $totalApplicants += $storeQuery->count();
                }
            }
        } else {
            // If appointed is Yes, count based on vacancyFills instead of applicants
            if (isset($filters['appointed']) && $filters['appointed'] === 'Yes') {
                $totalApplicants = $query->withCount(['vacancyFills' => function ($vacancyQuery) use ($startDate, $endDate) {
                    $vacancyQuery->whereBetween('created_at', [$startDate, $endDate]);
                }])->get()->sum('vacancy_fills_count');
            } else {
                $totalApplicants = $query->count();
            }
        }

        return $totalApplicants;
    }

    /**
     * Get the total number of filtered applicants appointed based on multiple criteria.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param array $filters An array of additional filters.
     * @param float $maxDistanceFromStore The maximum distance from the store in kilometers.
     * @return int The total count of filtered applicants appointed.
     */
    public function getTotalAppointedApplicantsFiltered(string $type = 'all', ?int $id = null, Carbon $startDate, Carbon $endDate, array $filters, float $maxDistanceFromStore = 50): int
    {
        // Retrieve the complete state ID
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return 0;
        }

        // Query `VacancyFill` instead of `Applicant`
        $query = VacancyFill::whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('applicant', function ($applicantQuery) use ($filters, $completeStateID) {
                // Apply applicant filters
                if (isset($filters['gender_id'])) {
                    $applicantQuery->where('gender_id', $filters['gender_id']);
                }
                if (isset($filters['race_id'])) {
                    $applicantQuery->where('race_id', $filters['race_id']);
                }
                if (isset($filters['education_id'])) {
                    $applicantQuery->where('education_id', $filters['education_id']);
                }
                if (isset($filters['experience_id'])) {
                    $applicantQuery->where('experience_id', $filters['experience_id']);
                }
                if (isset($filters['employment'])) {
                    $applicantQuery->where('employment', $filters['employment']);
                }

                // Age filter
                if (isset($filters['min_age']) && isset($filters['max_age'])) {
                    if ($filters['min_age'] == $filters['max_age']) {
                        $applicantQuery->where('age', $filters['max_age']);
                    } else {
                        $applicantQuery->whereBetween('age', [$filters['min_age'], $filters['max_age']]);
                    }
                }

                // Literacy, Numeracy, Situational, and Overall filters
                if (isset($filters['min_literacy']) && isset($filters['max_literacy'])) {
                    $applicantQuery->whereBetween('literacy_score', [$filters['min_literacy'], $filters['max_literacy']]);
                }
                if (isset($filters['min_numeracy']) && isset($filters['max_numeracy'])) {
                    $applicantQuery->whereBetween('numeracy_score', [$filters['min_numeracy'], $filters['max_numeracy']]);
                }
                if (isset($filters['min_situational']) && isset($filters['max_situational'])) {
                    $applicantQuery->whereBetween('situational_score', [$filters['min_situational'], $filters['max_situational']]);
                }
                if (isset($filters['min_overall']) && isset($filters['max_overall'])) {
                    $applicantQuery->whereBetween('overall_score', [$filters['min_overall'], $filters['max_overall']]);
                }

                // Completed filter
                if (isset($filters['completed'])) {
                    if ($filters['completed'] === 'Yes') {
                        $applicantQuery->where('state_id', '>=', $completeStateID);
                    } elseif ($filters['completed'] === 'No') {
                        $applicantQuery->where('state_id', '<', $completeStateID);
                    }
                } else {
                    $applicantQuery->where('state_id', '>=', $completeStateID);
                }

                // Shortlisted filter
                if (isset($filters['shortlisted'])) {
                    if ($filters['shortlisted'] === 'Yes') {
                        $applicantQuery->whereNotNull('shortlist_id');

                        // Apply geographic filters for shortlisted applicants
                        if (isset($filters['division_id'])) {
                            $applicantQuery->whereHas('shortlist.vacancy.store', function ($storeQuery) use ($filters) {
                                $storeQuery->where('division_id', $filters['division_id']);
                            });
                        } elseif (isset($filters['region_id'])) {
                            $applicantQuery->whereHas('shortlist.vacancy.store', function ($storeQuery) use ($filters) {
                                $storeQuery->where('region_id', $filters['region_id']);
                            });
                        } elseif (isset($filters['store_id'])) {
                            $applicantQuery->whereHas('shortlist.vacancy', function ($vacancyQuery) use ($filters) {
                                if (is_array($filters['store_id'])) {
                                    $vacancyQuery->whereIn('store_id', $filters['store_id']);
                                }
                            });
                        }
                    } elseif ($filters['shortlisted'] === 'No') {
                        $applicantQuery->whereNull('shortlist_id');
                    }
                }

                // Interviewed filter
                if (isset($filters['interviewed'])) {
                    if ($filters['interviewed'] === 'Yes') {
                        $applicantQuery->whereHas('interviews', function ($interviewQuery) {
                            $interviewQuery->whereNotNull('score');
                        });
                    } elseif ($filters['interviewed'] === 'No') {
                        $applicantQuery->where(function ($q) {
                            $q->doesntHave('interviews')
                                ->orWhereHas('interviews', function ($interviewQuery) {
                                    $interviewQuery->whereNull('score');
                                });
                        });
                    }
                }
            });

        // Apply geographic filters inside `VacancyFill`
        if (isset($filters['division_id'])) {
            $query->whereHas('vacancy.store', function ($storeQuery) use ($filters) {
                $storeQuery->where('division_id', $filters['division_id']);
            });
        } elseif (isset($filters['region_id'])) {
            $query->whereHas('vacancy.store', function ($storeQuery) use ($filters) {
                $storeQuery->where('region_id', $filters['region_id']);
            });
        } elseif (isset($filters['store_id']) && is_array($filters['store_id'])) {
            $query->whereHas('vacancy', function ($vacancyQuery) use ($filters) {
                $vacancyQuery->whereIn('store_id', $filters['store_id']);
            });
        }

        // Return count based on `VacancyFill` records (each appointment counted separately)
        return $query->count();
    }

    /**
     * Get the total number of applicants by month, filtered by type, date range, filters, and distance from a store, division, or region.
     *
     * @param string $type The type of filter (e.g., 'store', 'division', 'region', or 'all').
     * @param int|null $id The ID of the store, division, or region to filter (if applicable).
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param float $maxDistanceFromStore The maximum distance from the store in kilometers.
     * @param array $filters An array of additional filters to apply.
     * @return array An array of applicants grouped by month.
     */
    public function getTotalApplicantsByMonthFiltered(string $type = 'all', ?int $id = null, Carbon $startDate, Carbon $endDate, float $maxDistanceFromStore = 50, array $filters): array
    {
        // Initialize an array with months from startDate to endDate, set to 0
        $applicantsByMonth = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $applicantsByMonth[$currentDate->format('M')] = 0;
            $currentDate->addMonth();
        }

        // Retrieve the 'complete' state ID
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return $applicantsByMonth;
        }

        // Case 1: Appointed = 'Yes' - Count appointments from vacancy_fills
        if (isset($filters['appointed']) && $filters['appointed'] === 'Yes') {
            $query = DB::table('vacancy_fills')
                ->join('applicants', 'vacancy_fills.applicant_id', '=', 'applicants.id')
                ->join('vacancies', 'vacancy_fills.vacancy_id', '=', 'vacancies.id')
                ->join('stores', 'vacancies.store_id', '=', 'stores.id')
                ->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]);

            // Apply applicant filters
            $this->applyApplicantFilters($query, $filters, $completeStateID);

            // Apply geographic filters for appointed
            if (isset($filters['division_id'])) {
                $query->where('stores.division_id', $filters['division_id']);
            } elseif (isset($filters['region_id'])) {
                $query->where('stores.region_id', $filters['region_id']);
            } elseif (isset($filters['store_id']) && is_array($filters['store_id'])) {
                $query->whereIn('stores.id', $filters['store_id']);
            }

            // Apply shortlisted filter if set
            if (isset($filters['shortlisted']) && $filters['shortlisted'] === 'Yes') {
                $query->whereExists(function ($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('shortlists')
                        ->whereColumn('shortlists.applicant_id', 'applicants.id');
                });
            } elseif (isset($filters['shortlisted']) && $filters['shortlisted'] === 'No') {
                $query->whereNotExists(function ($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('shortlists')
                        ->whereColumn('shortlists.applicant_id', 'applicants.id');
                });
            }

            // Apply interviewed filter if set
            if (isset($filters['interviewed']) && $filters['interviewed'] === 'Yes') {
                $query->whereExists(function ($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('interviews')
                        ->whereColumn('interviews.applicant_id', 'applicants.id')
                        ->whereNotNull('interviews.score');
                });
            } elseif (isset($filters['interviewed']) && $filters['interviewed'] === 'No') {
                $query->whereNotExists(function ($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('interviews')
                        ->whereColumn('interviews.applicant_id', 'applicants.id')
                        ->whereNotNull('interviews.score');
                });
            }

            // Group and count by month
            $results = $query->selectRaw('DATE_FORMAT(vacancy_fills.created_at, "%b") as month, COUNT(*) as count')
                ->groupBy('month')
                ->pluck('count', 'month')
                ->toArray();
        } else {
            // Case 2: Non-appointed - Count applicants
            $query = Applicant::query()
                ->whereBetween('created_at', [$startDate, $endDate]);

            // Apply applicant filters
            $this->applyApplicantFilters($query, $filters, $completeStateID);

            // Apply shortlisted filter
            if (isset($filters['shortlisted'])) {
                if ($filters['shortlisted'] === 'Yes') {
                    $query->whereHas('shortlist', function ($shortlistQuery) use ($filters) {
                        if (isset($filters['division_id'])) {
                            $shortlistQuery->whereHas('vacancy.store', function ($storeQuery) use ($filters) {
                                $storeQuery->where('division_id', $filters['division_id']);
                            });
                        } elseif (isset($filters['region_id'])) {
                            $shortlistQuery->whereHas('vacancy.store', function ($storeQuery) use ($filters) {
                                $storeQuery->where('region_id', $filters['region_id']);
                            });
                        } elseif (isset($filters['store_id']) && is_array($filters['store_id'])) {
                            $shortlistQuery->whereHas('vacancy', function ($vacancyQuery) use ($filters) {
                                $vacancyQuery->whereIn('store_id', $filters['store_id']);
                            });
                        }
                    });
                } elseif ($filters['shortlisted'] === 'No') {
                    $query->whereDoesntHave('shortlist');
                }
            }

            // Apply interviewed filter
            if (isset($filters['interviewed'])) {
                if ($filters['interviewed'] === 'Yes') {
                    $query->whereHas('interviews', function ($interviewQuery) use ($filters) {
                        $interviewQuery->whereNotNull('score');
                        if (isset($filters['division_id'])) {
                            $interviewQuery->whereHas('vacancy.store', function ($storeQuery) use ($filters) {
                                $storeQuery->where('division_id', $filters['division_id']);
                            });
                        } elseif (isset($filters['region_id'])) {
                            $interviewQuery->whereHas('vacancy.store', function ($storeQuery) use ($filters) {
                                $storeQuery->where('region_id', $filters['region_id']);
                            });
                        } elseif (isset($filters['store_id']) && is_array($filters['store_id'])) {
                            $interviewQuery->whereHas('vacancy', function ($vacancyQuery) use ($filters) {
                                $vacancyQuery->whereIn('store_id', $filters['store_id']);
                            });
                        }
                    });
                } elseif ($filters['interviewed'] === 'No') {
                    $query->where(function ($q) {
                        $q->doesntHave('interviews')
                        ->orWhereHas('interviews', function ($interviewQuery) {
                            $interviewQuery->whereNull('score');
                        });
                    });
                }
            }

            // Apply distance-based filtering
            if (
                (!isset($filters['shortlisted']) || $filters['shortlisted'] === 'No') &&
                (!isset($filters['interviewed']) || $filters['interviewed'] === 'No') &&
                (!isset($filters['appointed']) || $filters['appointed'] === 'No') &&
                (isset($filters['store_id']) || isset($filters['region_id']) || isset($filters['division_id']))
            ) {
                $storeQuery = Store::query();
                if (isset($filters['division_id'])) {
                    $storeQuery->where('division_id', $filters['division_id']);
                } elseif (isset($filters['region_id'])) {
                    $storeQuery->where('region_id', $filters['region_id']);
                } elseif (isset($filters['store_id']) && is_array($filters['store_id'])) {
                    $storeQuery->whereIn('id', $filters['store_id']);
                }
                $storeIds = $storeQuery->pluck('id');

                if ($storeIds->isNotEmpty()) {
                    $query->whereExists(function ($subQuery) use ($storeIds, $maxDistanceFromStore) {
                        $subQuery->select(DB::raw(1))
                            ->from('stores')
                            ->whereIn('stores.id', $storeIds)
                            ->whereRaw("ST_Distance_Sphere(
                                point(SUBSTRING_INDEX(applicants.coordinates, ',', -1), SUBSTRING_INDEX(applicants.coordinates, ',', 1)),
                                point(SUBSTRING_INDEX(stores.coordinates, ',', -1), SUBSTRING_INDEX(stores.coordinates, ',', 1))
                            ) <= ?", [$maxDistanceFromStore * 1000]);
                    });
                }
            }

            // Group and count by month
            $results = $query->selectRaw('DATE_FORMAT(created_at, "%b") as month, COUNT(*) as count')
                ->groupBy('month')
                ->pluck('count', 'month')
                ->toArray();
        }

        // Merge results into the initialized array
        foreach ($results as $month => $count) {
            $applicantsByMonth[$month] = $count;
        }

        return $applicantsByMonth;
    }

    /**
     * Apply common applicant filters to the query.
     */
    private function applyApplicantFilters($query, array $filters, int $completeStateID): void
    {
        if (isset($filters['gender_id'])) {
            $query->where('applicants.gender_id', $filters['gender_id']);
        }
        if (isset($filters['race_id'])) {
            $query->where('applicants.race_id', $filters['race_id']);
        }
        if (isset($filters['education_id'])) {
            $query->where('applicants.education_id', $filters['education_id']);
        }
        if (isset($filters['experience_id'])) {
            $query->where('applicants.experience_id', $filters['experience_id']);
        }
        if (isset($filters['employment'])) {
            $query->where('applicants.employment', $filters['employment']);
        }
        if (isset($filters['min_age']) && isset($filters['max_age'])) {
            $query->whereBetween('applicants.age', [$filters['min_age'], $filters['max_age']]);
        }
        if (isset($filters['min_literacy']) && isset($filters['max_literacy'])) {
            $query->whereBetween('applicants.literacy_score', [$filters['min_literacy'], $filters['max_literacy']]);
        }
        if (isset($filters['min_numeracy']) && isset($filters['max_numeracy'])) {
            $query->whereBetween('applicants.numeracy_score', [$filters['min_numeracy'], $filters['max_numeracy']]);
        }
        if (isset($filters['min_situational']) && isset($filters['max_situational'])) {
            $query->whereBetween('applicants.situational_score', [$filters['min_situational'], $filters['max_situational']]);
        }
        if (isset($filters['min_overall']) && isset($filters['max_overall'])) {
            $query->whereBetween('applicants.overall_score', [$filters['min_overall'], $filters['max_overall']]);
        }

        // Apply completion filter
        if (isset($filters['completed'])) {
            if ($filters['completed'] === 'Yes') {
                $query->where('applicants.state_id', '>=', $completeStateID);
            } elseif ($filters['completed'] === 'No') {
                $query->where('applicants.state_id', '<', $completeStateID);
            }
        } else {
            $query->where('applicants.state_id', '>=', $completeStateID);
        }
    }
}
