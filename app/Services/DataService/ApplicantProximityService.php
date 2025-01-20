<?php

namespace App\Services\DataService;

use App\Models\State;
use App\Models\Store;
use App\Models\Vacancy;
use App\Models\Applicant;
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
        // Get the stores based on the type (store, division, or region)
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
            return 0; // Return 0 if no stores are found for the given filter
        }

        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return 0; // Handle case where 'complete' state does not exist
        }

        $totalDistance = 0;
        $applicantCount = 0;

        // Loop through each store and calculate the applicants' distances within the given range
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

                // Calculate the total distance for each applicant
                foreach ($applicants as $applicant) {
                    if ($applicant->coordinates) {
                        $applicantCoordinates = explode(',', $applicant->coordinates);
                        $applicantLat = floatval($applicantCoordinates[0]);
                        $applicantLng = floatval($applicantCoordinates[1]);

                        // Calculate the distance between the store and the applicant
                        $distance = $this->calculateDistance($storeLat, $storeLng, $applicantLat, $applicantLng);
                        $totalDistance += $distance;
                        $applicantCount++;
                    }
                }
            }
        }

        // Calculate the average distance and return it
        if ($applicantCount > 0) {
            return round($totalDistance / $applicantCount, 1); // Average distance rounded to 2 decimal places
        } else {
            return 0; // Return 0 if no applicants are found
        }
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
        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return 0; // Handle case where 'complete' state does not exist
        }

        // Check if the type is 'all' to get all applicants within the date range
        if ($type === 'all') {
            return Applicant::whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID)
                ->count(); // Simply return all applicants within the date range, ignoring distance
        }

        // Otherwise, proceed with filtering by store, division, or region
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
            return 0; // Return 0 if no stores are found for the given filter
        }

        $applicantCount = 0;

        // Loop through each store and calculate the applicants within the given distance
        foreach ($stores as $store) {
            if ($store->coordinates) {
                $storeCoordinates = explode(',', $store->coordinates);
                $storeLat = floatval($storeCoordinates[0]);
                $storeLng = floatval($storeCoordinates[1]);

                // Count the applicants within the distance range using MySQL ST_Distance_Sphere
                $storeApplicantCount = Applicant::whereBetween('created_at', [$startDate, $endDate])
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
                    ->count();

                $applicantCount += $storeApplicantCount;
            }
        }

        return $applicantCount;
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
        // Initialize an array to hold the results, with months and years set to 0 from startDate to endDate
        $applicantsByMonth = [];
        $currentDate = $startDate->copy();

        // Loop to populate only the months and years between startDate and endDate
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

        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return $applicantsByMonth; // Return if 'complete' state does not exist
        }

        if ($type === 'all') {
            $applicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID)
                ->get();

            foreach ($applicants as $applicant) {
                $monthYear = $applicant->created_at->format("M'y"); // Format as Jan'24
                if (isset($applicantsByMonth[$monthYear])) {
                    $applicantsByMonth[$monthYear]++;
                }
            }

            return $applicantsByMonth;
        }

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
            return $applicantsByMonth;
        }

        foreach ($stores as $store) {
            if ($store->coordinates) {
                $storeCoordinates = explode(',', $store->coordinates);
                $storeLat = floatval($storeCoordinates[0]);
                $storeLng = floatval($storeCoordinates[1]);

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
                    ", [$storeLng, $storeLat, $maxDistanceFromStore * 1000])
                    ->get();

                foreach ($applicants as $applicant) {
                    $monthYear = $applicant->created_at->format("M'y"); // Format as Jan'24
                    if (isset($applicantsByMonth[$monthYear])) {
                        $applicantsByMonth[$monthYear]++;
                    }
                }
            }
        }

        return $applicantsByMonth;
    }
}
