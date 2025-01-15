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
        // Initialize the query to filter applicants who are appointed (i.e., `appointed_id` is not null)
        $query = Applicant::whereNotNull('appointed_id')
            ->whereHas('vacanciesFilled', function ($vacancyQuery) use ($type, $id, $startDate, $endDate) {
                // Apply the date range filter on the `vacancy_fills.created_at`
                $vacancyQuery->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]);

                // Apply additional filtering based on the type
                if ($type === 'store') {
                    $vacancyQuery->where('store_id', $id);
                } elseif ($type === 'division') {
                    $vacancyQuery->where('division_id', $id);
                } elseif ($type === 'region') {
                    $vacancyQuery->where('region_id', $id);
                }
            });

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
        // Initialize an array to hold the results, with months set to 0 from startDate to endDate
        $applicantsByMonth = [];
        $currentDate = $startDate->copy();

        // Loop to populate only the months between startDate and endDate
        while ($currentDate->lte($endDate)) {
            $monthName = $currentDate->format('M');
            $applicantsByMonth[$monthName] = 0;
            $currentDate->addMonth();
        }

        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return $applicantsByMonth; // Return if 'complete' state does not exist
        }

        // If the type is 'all', retrieve all applicants within the date range and group them by month
        if ($type === 'all') {
            $applicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID)
                ->get();

            // Group applicants by the month of their creation date and count them
            foreach ($applicants as $applicant) {
                $month = $applicant->created_at->format('M');
                $applicantsByMonth[$month]++;
            }

            return $applicantsByMonth;
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

        if ($stores->isEmpty()) {
            return $applicantsByMonth; // Return the array with months initialized to 0 if no stores found
        }

        // Loop through each store and calculate the applicants by month within the given distance
        foreach ($stores as $store) {
            if ($store->coordinates) {
                $storeCoordinates = explode(',', $store->coordinates);
                $storeLat = floatval($storeCoordinates[0]);
                $storeLng = floatval($storeCoordinates[1]);

                // Retrieve applicants within the distance range using MySQL ST_Distance_Sphere
                $applicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
                    ->where('state_id', '>=', $completeStateID)
                    ->whereRaw("
                        ST_Distance_Sphere(
                            point(
                                SUBSTRING_INDEX(applicants.coordinates, ',', -1), 
                                SUBSTRING_INDEX(applicants.coordinates, ',', 1)
                            ), 
                            point(?, ?)
                        ) <= ?
                    ", [$storeLng, $storeLat, $maxDistanceFromStore * 1000]) // Multiply by 1000 to convert km to meters
                    ->get();

                // Group applicants by the month of their creation date and count them
                foreach ($applicants as $applicant) {
                    $month = $applicant->created_at->format('M');
                    $applicantsByMonth[$month]++;
                }
            }
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
        // Initialize an array to hold the results, with months set to 0 from startDate to endDate
        $applicantsByMonth = [];
        $currentDate = $startDate->copy();

        // Loop to populate only the months between startDate and endDate
        while ($currentDate->lte($endDate)) {
            $monthName = $currentDate->format('M');
            $applicantsByMonth[$monthName] = 0;
            $currentDate->addMonth();
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
        // Initialize an array to hold the results, with months set to 0 for each gender from startDate to endDate
        $applicantsGenderByMonth = [];
        $currentDate = $startDate->copy();

        // Retrieve all available genders
        $genders = Gender::all();

        // Loop to populate the array for each month and gender
        while ($currentDate->lte($endDate)) {
            $monthName = $currentDate->format('M');
            foreach ($genders as $gender) {
                // Set initial count for each month and gender to 0
                $applicantsGenderByMonth[$gender->name][$monthName] = 0;
            }
            $currentDate->addMonth();
        }

        // Retrieve the complete state ID
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return $applicantsGenderByMonth; // Return if 'complete' state does not exist
        }

        // If the type is 'all', retrieve all applicants with a non-null gender_id within the date range and group them by month and gender
        if ($type === 'all') {
            $applicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID)
                ->whereNotNull('gender_id')  // Exclude applicants with null gender_id
                ->get();

            // Group applicants by the month of their creation date and gender, then count them
            foreach ($applicants as $applicant) {
                $month = $applicant->created_at->format('M');
                $genderName = $applicant->gender->name; // Get gender name directly
                $applicantsGenderByMonth[$genderName][$month]++;
            }

            return $applicantsGenderByMonth;
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

        // If no stores are found, return the array with months initialized to 0 for each gender
        if ($stores->isEmpty()) {
            return $applicantsGenderByMonth;
        }

        // Loop through each store and calculate the applicants by month and gender within the given distance
        foreach ($stores as $store) {
            if ($store->coordinates) {
                // Extract latitude and longitude from store coordinates
                $storeCoordinates = explode(',', $store->coordinates);
                $storeLat = floatval($storeCoordinates[0]);
                $storeLng = floatval($storeCoordinates[1]);

                // Retrieve applicants within the distance range using MySQL ST_Distance_Sphere
                $applicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
                    ->where('state_id', '>=', $completeStateID)
                    ->whereNotNull('gender_id') // Exclude applicants with null gender_id
                    ->whereRaw("
                        ST_Distance_Sphere(
                            point(
                                SUBSTRING_INDEX(applicants.coordinates, ',', -1), 
                                SUBSTRING_INDEX(applicants.coordinates, ',', 1)
                            ), 
                            point(?, ?)
                        ) <= ?
                    ", [$storeLng, $storeLat, $maxDistanceFromStore * 1000]) // Multiply by 1000 to convert km to meters
                    ->get();

                // Group applicants by the month of their creation date and gender, then count them
                foreach ($applicants as $applicant) {
                    $month = $applicant->created_at->format('M');
                    $genderName = $applicant->gender->name; // Get gender name directly
                    $applicantsGenderByMonth[$genderName][$month]++;
                }
            }
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
        // Initialize an array to hold the results, with months set to 0 for each race from startDate to endDate
        $applicantsRaceByMonth = [];
        $currentDate = $startDate->copy();

        // Retrieve all available races
        $races = Race::all();

        // Loop to populate the array for each month and race
        while ($currentDate->lte($endDate)) {
            $monthName = $currentDate->format('M');
            foreach ($races as $race) {
                // Set initial count for each month and race to 0
                $applicantsRaceByMonth[$race->name][$monthName] = 0;
            }
            $currentDate->addMonth();
        }

        // Retrieve the complete state ID
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return $applicantsRaceByMonth; // Return if 'complete' state does not exist
        }

        // If the type is 'all', retrieve all applicants with a non-null race_id within the date range and group them by month and race
        if ($type === 'all') {
            $applicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID)
                ->whereNotNull('race_id')  // Exclude applicants with null race_id
                ->get();

            // Group applicants by the month of their creation date and race, then count them
            foreach ($applicants as $applicant) {
                $month = $applicant->created_at->format('M');
                $raceName = $applicant->race->name; // Get race name directly
                $applicantsRaceByMonth[$raceName][$month]++;
            }

            return $applicantsRaceByMonth;
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

        // If no stores are found, return the array with months initialized to 0 for each race
        if ($stores->isEmpty()) {
            return $applicantsRaceByMonth;
        }

        // Loop through each store and calculate the applicants by month and race within the given distance
        foreach ($stores as $store) {
            if ($store->coordinates) {
                // Extract latitude and longitude from store coordinates
                $storeCoordinates = explode(',', $store->coordinates);
                $storeLat = floatval($storeCoordinates[0]);
                $storeLng = floatval($storeCoordinates[1]);

                // Retrieve applicants within the distance range using MySQL ST_Distance_Sphere
                $applicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
                    ->where('state_id', '>=', $completeStateID)
                    ->whereNotNull('race_id') // Exclude applicants with null race_id
                    ->whereRaw("
                        ST_Distance_Sphere(
                            point(
                                SUBSTRING_INDEX(applicants.coordinates, ',', -1), 
                                SUBSTRING_INDEX(applicants.coordinates, ',', 1)
                            ), 
                            point(?, ?)
                        ) <= ?
                    ", [$storeLng, $storeLat, $maxDistanceFromStore * 1000]) // Multiply by 1000 to convert km to meters
                    ->get();

                // Group applicants by the month of their creation date and race, then count them
                foreach ($applicants as $applicant) {
                    $month = $applicant->created_at->format('M');
                    $raceName = $applicant->race->name; // Get race name directly
                    $applicantsRaceByMonth[$raceName][$month]++;
                }
            }
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
            $query->whereNotNull('appointed_id') // Ensure only appointed applicants
                ->whereHas('vacanciesFilled', function ($vacancyQuery) use ($filters, $startDate, $endDate) {
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
            // If no specific store, division, or region is set, count all applicants
            $totalApplicants = $query->count();
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

        // Base query to filter applicants who are appointed and within the specified vacancy_fills date range
        $query = Applicant::whereNotNull('appointed_id')
            ->whereHas('vacanciesFilled', function ($vacancyQuery) use ($type, $id, $startDate, $endDate) {
                // Apply the date range filter on the vacancy_fills created_at
                $vacancyQuery->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]);

                // Apply geographic filters
                if (isset($filters['division_id'])) {
                    $stores = Store::where('division_id', $filters['division_id'])->get();
                    if ($stores->isEmpty()) {
                        return; // Exit early if no stores match
                    }
                    $vacancyQuery->whereIn('store_id', $stores->pluck('id'));
                } elseif (isset($filters['region_id'])) {
                    $stores = Store::where('region_id', $filters['region_id'])->get();
                    if ($stores->isEmpty()) {
                        return; // Exit early if no stores match
                    }
                    $vacancyQuery->whereIn('store_id', $stores->pluck('id'));
                } elseif (isset($filters['store_id'])) {
                    if (is_array($filters['store_id'])) {
                        $vacancyQuery->whereIn('store_id', $filters['store_id']);
                    }
                }
            });

        // Apply additional filters
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

        // Age filter
        if (isset($filters['min_age']) && isset($filters['max_age'])) {
            if ($filters['min_age'] == $filters['max_age']) {
                $query->where('age', $filters['max_age']);
            } else {
                $query->whereBetween('age', [$filters['min_age'], $filters['max_age']]);
            }
        }

        // Literacy, Numeracy, Situational, and Overall filters
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

        // Default: Count all applicants appointed within the date range
        $totalApplicants = $query->count();

        return $totalApplicants;
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
        // Initialize an array to hold monthly counts, with each month from startDate to endDate set to 0
        $applicantsByMonth = [];
        $currentDate = $startDate->copy();
        $currentEndDate = $endDate->copy();

        while ($currentDate->lte($currentEndDate->endOfMonth())) {
            $monthName = $currentDate->format('M');
            $applicantsByMonth[$monthName] = 0;
            $currentDate->addMonth();
        }

        // Retrieve the ID for the 'complete' state
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return $applicantsByMonth;
        }

        // Base query for applicants
        $query = Applicant::query();

        // Apply additional filters
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

        // Apply range filters for age, literacy, numeracy, situational, and overall scores
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
            $query->whereNotNull('appointed_id') // Ensure only appointed applicants
                ->whereHas('vacanciesFilled', function ($vacancyQuery) use ($filters, $startDate, $endDate) {
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

                    foreach ($storeQuery->get() as $applicant) {
                        $month = $applicant->created_at->format('M');
                        $applicantsByMonth[$month]++;
                    }
                }
            }
        } else {
            // Group applicants directly by month
            foreach ($query->get() as $applicant) {
                $month = $applicant->created_at->format('M');
                $applicantsByMonth[$month]++;
            }
        }

        return $applicantsByMonth;
    }
}
