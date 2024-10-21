<?php

namespace App\Services\DataService;

use App\Models\State;
use App\Models\Store;
use App\Models\Vacancy;
use App\Models\Applicant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApplicantProximityService
{
    /*
    |--------------------------------------------------------------------------
    | Average Distance Applicants Appointed
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Calculate Distance
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Talent Pool Applicants
    |--------------------------------------------------------------------------
    */

    /**
     * Count the number of talent pool applicants within a given distance from the store, division, or region.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param float $maxDistanceFromStore The maximum distance from the store in kilometers.
     * @return int The count of talent pool applicants within the given distance.
     */
    public function getTalentPoolApplicants(string $type, ?int $id, $startDate, $endDate, $maxDistanceFromStore): int
    {
        // Get the store, division, or region based on the type
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

    /*
    |--------------------------------------------------------------------------
    | Talent Pool Applicants By Month
    |--------------------------------------------------------------------------
    */

    /**
     * Get the number of talent pool applicants by month within a given distance from the store, division, or region.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param float $maxDistanceFromStore The maximum distance from the store in kilometers.
     * @return array An array of applicants by month.
     */
    public function getTalentPoolApplicantsByMonth(string $type, ?int $id, $startDate, $endDate, $maxDistanceFromStore): array
    {
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
            return []; // Return empty array if no stores found for the given filter
        }

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
            return []; // Handle case where 'complete' state does not exist
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
                    // Get the month name from the created_at date (e.g., 'Jan', 'Feb', etc.)
                    $month = $applicant->created_at->format('M');
                    // Increment the count for the corresponding month
                    $applicantsByMonth[$month]++;
                }
            }
        }

        return $applicantsByMonth;
    }
}
