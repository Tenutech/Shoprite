<?php

namespace App\Services\DataService;

use App\Models\State;
use App\Models\Store;
use App\Models\Vacancy;
use App\Models\Applicant;
use App\Models\Statistic;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplicantProximityService
{
    /**
     * Calculate the average distance of talent pool applicants within a given distance from the store, division, or region.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param float $maxDistanceFromStore The maximum distance from the store in kilometers.
     * @return float The average distance of applicants in kilometers.
     */
    public function getAverageDistanceTalentPoolApplicants(string $type, ?int $id, $startDate, $endDate, $maxDistanceFromStore): float
    {
        // Fetch stores based on the filter type
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
            return 0.0;
        }

        // Get the 'complete' state ID (assumed to be a threshold for completed applications)
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return 0.0;
        }

        $totalDistance = 0.0;
        $applicantCount = 0;

        foreach ($stores as $store) {
            if ($store->coordinates) {
                // Parse store coordinates (assuming format "lat,lng")
                [$storeLat, $storeLng] = array_map('floatval', explode(',', $store->coordinates));

                // Single query to aggregate distance and count
                $result = Applicant::whereBetween('created_at', [$startDate, $endDate])
                    ->where('state_id', '>=', $completeStateID)
                    ->whereNotNull('coordinates')
                    ->whereRaw("coordinates REGEXP '^-?[0-9]+(\.[0-9]+)?,-?[0-9]+(\.[0-9]+)?$'") // Validate format
                    ->selectRaw("
                        SUM(ST_Distance_Sphere(
                            POINT(
                                CAST(SUBSTRING_INDEX(coordinates, ',', -1) AS DECIMAL(10,7)),
                                CAST(SUBSTRING_INDEX(coordinates, ',', 1) AS DECIMAL(10,7))
                            ),
                            POINT(?, ?)
                        ) / 1000) AS total_distance,
                        COUNT(*) AS applicant_count
                    ", [$storeLng, $storeLat])
                    ->whereRaw("
                        ST_Distance_Sphere(
                            POINT(
                                CAST(SUBSTRING_INDEX(coordinates, ',', -1) AS DECIMAL(10,7)),
                                CAST(SUBSTRING_INDEX(coordinates, ',', 1) AS DECIMAL(10,7))
                            ),
                            POINT(?, ?)
                        ) <= ?
                    ", [$storeLng, $storeLat, $maxDistanceFromStore * 1000])
                    ->first();

                $totalDistance += $result->total_distance ?? 0.0;
                $applicantCount += $result->applicant_count ?? 0;
            }
        }

        return $applicantCount > 0 ? round($totalDistance / $applicantCount, 1) : 0.0;
    }

    /**
     * Calculate the average distance of talent pool applicants within a given distance from the store, division, or region.
     *
     * This method retrieves the relevant stores based on the filter type (store, division, or region)
     * and, for each store that has coordinates, uses a database aggregate query to calculate the
     * total distance (using MySQL's ST_Distance_Sphere) and the number of applicants within the
     * specified maximum distance (converted from kilometers to meters). The sum of these distances
     * (converted to kilometers) and the total count of applicants are then used to compute the overall
     * average distance. If no applicants are found or if no matching stores exist, the method returns 0.
     *
     * @param string $type The type of filter (e.g., 'store', 'division', or 'region').
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param float $maxDistanceFromStore The maximum distance from the store in kilometers.
     * @return float The average distance of applicants in kilometers.
     */
    public function getAverageDistanceTalentPoolApplicantsDB(string $type, ?int $id, $startDate, $endDate, $maxDistanceFromStore): float
    {
        // Retrieve stores based on the filter type (store, division, or region)
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

        // If no stores are found, return 0.
        if ($stores->isEmpty()) {
            return 0;
        }

        // Retrieve the state ID corresponding to the 'complete' state.
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return 0; // Handle case where the 'complete' state does not exist.
        }

        $totalDistanceKm = 0;
        $totalApplicantCount = 0;

        // Loop over each store to calculate aggregate distance metrics via the database.
        foreach ($stores as $store) {
            if ($store->coordinates) {
                // Parse the store's coordinates (expected format: "lat,lng")
                $storeCoordinates = explode(',', $store->coordinates);
                $storeLat = floatval($storeCoordinates[0]);
                $storeLng = floatval($storeCoordinates[1]);

                // Run an aggregate query to compute the total distance (in meters) and count of applicants
                // within the specified maximum distance from the store.
                $applicantStats = Applicant::selectRaw("
                        SUM(ST_Distance_Sphere(
                            POINT(
                                SUBSTRING_INDEX(coordinates, ',', -1),
                                SUBSTRING_INDEX(coordinates, ',', 1)
                            ), 
                            POINT(?, ?)
                        )) as total_distance,
                        COUNT(*) as applicant_count
                    ", [$storeLng, $storeLat])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('state_id', '>=', $completeStateID)
                    ->whereRaw("
                        ST_Distance_Sphere(
                            POINT(
                                SUBSTRING_INDEX(coordinates, ',', -1),
                                SUBSTRING_INDEX(coordinates, ',', 1)
                            ), 
                            POINT(?, ?)
                        ) <= ?
                    ", [$storeLng, $storeLat, $maxDistanceFromStore * 1000]) // Convert km to meters.
                    ->first();

                // If applicants were found for this store, update the totals.
                if ($applicantStats && $applicantStats->applicant_count > 0) {
                    // Convert the summed distance from meters to kilometers.
                    $storeTotalDistanceKm = $applicantStats->total_distance / 1000;
                    $totalDistanceKm += $storeTotalDistanceKm;
                    $totalApplicantCount += $applicantStats->applicant_count;
                }
            }
        }

        // Calculate and return the overall average distance, rounded to one decimal place.
        return $totalApplicantCount > 0 ? round($totalDistanceKm / $totalApplicantCount, 1) : 0;
    }

    /**
     * Retrieve the stored average distance of talent pool applicants from the statistics table.
     *
     * This method queries the statistics table for the latest entry where the name is 'average_distance_talent_pool'.
     * If no record is found, it returns 0.
     *
     * @return float The stored average distance of talent pool applicants.
     */
    public function getAverageDistanceTalentPoolApplicantsStatistic(string $type, ?int $id, $startDate, $endDate, $maxDistanceFromStore): float
    {
        // Start with the base query for statistics
        $query = Statistic::where('name', 'average_distance_talent_pool');

        // Apply filters based on the type
        switch ($type) {
            case 'all':
                $query->whereIn('role_id', [1, 2]);
                break;

            case 'region':
                $query->where('role_id', 3)
                    ->where('region_id', $id);
                break;

            case 'division':
                $query->whereIn('role_id', [4, 5])
                    ->where('division_id', $id);
                break;            

            case 'store':
                $query->where('role_id', 6)
                    ->where('store_id', $id);
                break;
        }

        // Retrieve the most recent value
        return $query->latest('created_at')->value('value') ?? 0;
    }


    /**
     * Calculate the average distance between stores' coordinates and appointed applicants' coordinates for store, division, or region.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @return float The average distance in kilometers.
     */
    public function getAverageDistanceApplicantsAppointed(string $type, ?int $id, $startDate, $endDate): float
    {
        $totalDistance = 0;
        $applicantCount = 0;

        // Retrieve vacancies and stores based on the type (store, division, region) and date range
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
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('deleted', 'No')
            ->with(['store', 'appointed']) // Load store and appointed applicants relationships
            ->get();

        // Loop through each vacancy and calculate the distance for all appointed applicants
        foreach ($vacancies as $vacancy) {
            $store = $vacancy->store;

            // Ensure the store has valid coordinates
            if ($store && $store->coordinates) {
                $storeCoordinates = explode(',', $store->coordinates); // Assuming coordinates are stored as "latitude,longitude"
                $storeLat = floatval($storeCoordinates[0]);
                $storeLng = floatval($storeCoordinates[1]);

                foreach ($vacancy->appointed as $applicant) {
                    // Assuming applicants have a 'coordinates' field in the format "latitude,longitude"
                    if ($applicant->coordinates) {
                        $applicantCoordinates = explode(',', $applicant->coordinates);
                        $applicantLat = floatval($applicantCoordinates[0]);
                        $applicantLng = floatval($applicantCoordinates[1]);

                        // Calculate the distance between the store and the applicant in kilometers
                        $distance = $this->calculateDistance($storeLat, $storeLng, $applicantLat, $applicantLng);
                        $totalDistance += $distance;
                        $applicantCount++;
                    }
                }
            }
        }

        // Calculate the average distance and round it to 1 decimal place
        if ($applicantCount > 0) {
            return round($totalDistance / $applicantCount, 1);
        } else {
            return 0; // Return 0 if no appointed applicants are found
        }
    }

    /**
     * Calculate the distance between two coordinates (latitude and longitude) in kilometers.
     *
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Radius of the Earth in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in kilometers
    }

    /**
     * Count the number of talent pool applicants within a given distance from the store, division, or region, or all applicants if type is 'all'.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param float $maxDistanceFromStore The maximum distance from the store in kilometers.
     * @return int The count of talent pool applicants within the given distance or all applicants if type is 'all'.
     */
    public function getTalentPoolApplicants(string $type, ?int $id, $startDate, $endDate, $maxDistanceFromStore): int
    {
        // Retrieve the complete state ID
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return 0; // Handle case where 'complete' state does not exist
        }

        if ($type === 'all') {
            return Applicant::whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID)
                ->count();
        }

        // Count unique applicants within distance of at least one relevant store
        return Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '>=', $completeStateID)
            ->whereExists(function ($query) use ($type, $id, $maxDistanceFromStore) {
                $query->select(DB::raw(1))
                    ->from('stores')
                    ->where(function ($q) use ($type, $id) {
                        if ($type === 'store') {
                            $q->where('id', $id);
                        } elseif ($type === 'division') {
                            $q->where('division_id', $id);
                        } elseif ($type === 'region') {
                            $q->where('region_id', $id);
                        }
                    })
                    ->whereRaw("ST_Distance_Sphere(
                        point(
                            SUBSTRING_INDEX(applicants.coordinates, ',', -1),
                            SUBSTRING_INDEX(applicants.coordinates, ',', 1)
                        ),
                        point(
                            SUBSTRING_INDEX(stores.coordinates, ',', -1),
                            SUBSTRING_INDEX(stores.coordinates, ',', 1)
                        )
                    ) <= ?", [$maxDistanceFromStore * 1000]);
            })
            ->count();
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
    public function getTalentPoolApplicantsByMonth(string $type = null, ?int $id = null, $startDate, $endDate, $maxDistanceFromStore): array
    {
        // Initialize an array with months and years set to 0 from startDate to endDate
        $applicantsByMonth = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $monthYear = $currentDate->format("M'y"); // Format as Jan'24
            $applicantsByMonth[$monthYear] = 0;
            $currentDate->addMonth();
        }

        $lastMonthYear = $endDate->format("M'y");
        if (!array_key_exists($lastMonthYear, $applicantsByMonth)) {
            $applicantsByMonth[$lastMonthYear] = 0;
        }

        // Retrieve the 'complete' state ID
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return $applicantsByMonth; // Return if 'complete' state does not exist
        }

        if ($type === 'all') {
            // Single query for all applicants, grouped by month-year
            $applicants = Applicant::selectRaw("DATE_FORMAT(created_at, '%b''%y') as month_year, COUNT(*) as count")
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID)
                ->groupBy('month_year')
                ->get();

            foreach ($applicants as $applicant) {
                $applicantsByMonth[$applicant->month_year] = $applicant->count;
            }

            return $applicantsByMonth;
        }

        // For store, division, or region types, use a single query with spatial filtering
        $applicants = Applicant::selectRaw("DATE_FORMAT(applicants.created_at, '%b''%y') as month_year, COUNT(DISTINCT applicants.id) as count")
            ->whereBetween('applicants.created_at', [$startDate, $endDate])
            ->where('applicants.state_id', '>=', $completeStateID)
            ->whereExists(function ($query) use ($type, $id, $maxDistanceFromStore) {
                $query->select(DB::raw(1))
                    ->from('stores')
                    ->where(function ($q) use ($type, $id) {
                        if ($type === 'store') {
                            $q->where('stores.id', $id);
                        } elseif ($type === 'division') {
                            $q->where('stores.division_id', $id);
                        } elseif ($type === 'region') {
                            $q->where('stores.region_id', $id);
                        }
                    })
                    ->whereRaw("ST_Distance_Sphere(
                        point(
                            SUBSTRING_INDEX(applicants.coordinates, ',', -1),
                            SUBSTRING_INDEX(applicants.coordinates, ',', 1)
                        ),
                        point(
                            SUBSTRING_INDEX(stores.coordinates, ',', -1),
                            SUBSTRING_INDEX(stores.coordinates, ',', 1)
                        )
                    ) <= ?", [$maxDistanceFromStore * 1000]);
            })
            ->groupBy('month_year')
            ->get();

        foreach ($applicants as $applicant) {
            $applicantsByMonth[$applicant->month_year] = $applicant->count;
        }

        return $applicantsByMonth;
    }
}
