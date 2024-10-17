<?php

namespace App\Services\DataService;

use App\Models\State;
use App\Models\Store;
use App\Models\Vacancy;
use App\Models\Interview;
use App\Models\Shortlist;
use App\Models\Applicant;
use Illuminate\Support\Facades\DB;

class ApplicantProximityService
{
    /**
     * Calculate the average distance between the store's coordinates and appointed applicants' coordinates.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return float
     */
    public function getStoreAverageDistanceApplicantsAppointed(int $storeId, $startDate, $endDate)
    {
        // Retrieve the store with coordinates
        $store = Store::find($storeId);
        if (!$store || !$store->coordinates) {
            return 0; // Return 0 if store or coordinates are not available
        }

        $storeCoordinates = explode(',', $store->coordinates); // Assuming coordinates are stored as "latitude,longitude"
        $storeLat = floatval($storeCoordinates[0]);
        $storeLng = floatval($storeCoordinates[1]);

        $totalDistance = 0;
        $applicantCount = 0;

        // Retrieve all vacancies for the store within the specified date range
        $vacancies = Vacancy::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('appointed') // Load the appointed relationship
            ->get();

        // Loop through each vacancy and its appointed applicants
        foreach ($vacancies as $vacancy) {
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
     * Count the number of talent pool applicants within a given distance from the store.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @param float $maxDistanceFromStore
     * @return int
     */
    public function getStoreTalentPoolApplicants(int $storeId, $startDate, $endDate, $maxDistanceFromStore)
    {
        // Retrieve the store's coordinates
        $store = Store::find($storeId);
        if (!$store || !$store->coordinates) {
            return 0; // Return 0 if store or coordinates are not available
        }

        $storeCoordinates = explode(',', $store->coordinates);
        $storeLat = floatval($storeCoordinates[0]);
        $storeLng = floatval($storeCoordinates[1]);

        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');

        if (!$completeStateID) {
            return 0; // Handle case where 'complete' state does not exist
        }

        // Count the applicants within the distance range using MySQL ST_Distance_Sphere
        $applicantCount = Applicant::whereBetween('created_at', [$startDate, $endDate])
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

        return $applicantCount;
    }

    /**
     * Get the number of talent pool applicants by month within a given distance from the store.
     *
     * @param int $storeId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @param float $maxDistanceFromStore
     * @return array
     */
    public function getStoreTalentPoolApplicantsByMonth(int $storeId, $startDate, $endDate, $maxDistanceFromStore)
    {
        // Retrieve the store's coordinates
        $store = Store::find($storeId);
        if (!$store || !$store->coordinates) {
            return []; // Return empty if store or coordinates are not available
        }

        $storeCoordinates = explode(',', $store->coordinates);
        $storeLat = floatval($storeCoordinates[0]);
        $storeLng = floatval($storeCoordinates[1]);

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

        return $applicantsByMonth;
    }

    /**
     * Calculate the average proximity for all applicants for Admin view.
     *
     * @param string $startDate The start date for filtering applicants.
     * @param string $endDate The end date for filtering applicants.
     * @return float The average distance in kilometers.
     */
    public function calculateProximityForAdmin(string $startDate, string $endDate): float
    {
        return $this->calculateAverageDistance('national', null, $startDate, $endDate);
    }

    /**
     * Calculate the average proximity for applicants for division
     *
     * @param int $divisionId The ID of the division.
     * @param string $startDate The start date for filtering applicants.
     * @param string $endDate The end date for filtering applicants.
     * @return float The average distance in kilometers.
     */
    public function calculateProximityForDivision(int $divisionId, string $startDate, string $endDate): float
    {
        return $this->calculateAverageDistance('division', $divisionId, $startDate, $endDate);
    }

    /**
     * Calculate the average proximity for a given region
     *
     * @param int $regionId The ID of the region.
     * @param string $startDate The start date for filtering applicants.
     * @param string $endDate The end date for filtering applicants.
     * @return float The average distance in kilometers.
     */
    public function calculateProximityForRegion(int $regionId, string $startDate, string $endDate): float
    {
        return $this->calculateAverageDistance('region', $regionId, $startDate, $endDate);
    }

    /**
     * Calculate the average proximity for applicants for store
     *
     * @param int $storeId The ID of the store.
     * @param string $startDate The start date for filtering applicants.
     * @param string $endDate The end date for filtering applicants.
     * @return float The average distance in kilometers.
     */
    public function calculateProximityForStore(int $storeId, string $startDate, string $endDate): float
    {
        return $this->calculateAverageDistance('store', $storeId, $startDate, $endDate);
    }

    /**
     * Fetch applicants in the talent pool within a specified distance from a store.
     *
     * @param string $type The type of view (e.g., national, division, region, store).
     * @param int|null $id The ID for filtering based on the type.
     * @param int $distanceLimit The distance limit in kilometers.
     * @param string $startDate The start date for filtering applicants (YYYY-MM-DD).
     * @param string $endDate The end date for filtering applicants (YYYY-MM-DD).
     * @return float The average distance of applicants in the talent pool.
     */
    public function calculateTalentPoolDistance(string $type, ?int $id, int $distanceLimit, string $startDate, string $endDate): float
    {
        $talentPool = DB::table('vacancy_fills')
            ->join('applicants', 'vacancy_fills.applicant_id', '=', 'applicants.id')
            ->join('vacancies', 'vacancy_fills.vacancy_id', '=', 'vacancies.id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->select(
                'applicants.coordinates as applicant_coordinates',
                'stores.coordinates as store_coordinates'
            )
            ->get();

        $totalDistance = 0;
        $validApplicantsCount = 0;

        foreach ($talentPool as $applicant) {
            $applicantCoords = $this->splitCoordinates($applicant->applicant_coordinates);
            $storeCoords = $this->splitCoordinates($applicant->store_coordinates);

            $distance = $this->haversineGreatCircleDistance(
                $applicantCoords['lat'],
                $applicantCoords['long'],
                $storeCoords['lat'],
                $storeCoords['long']
            );

            if ($distance <= $distanceLimit) {
                $totalDistance += $distance;
                $validApplicantsCount++;
            }
        }

        $totalDistance = ($validApplicantsCount > 0) ? ($totalDistance / $validApplicantsCount) : 0;

        return number_format($totalDistance, 2);
    }

    /**
     * Calculate the average distance for placed applicants based on the specified type and date range.
     *
     * @param string $type The type of view (e.g., national, division, region, store).
     * @param int|null $id The ID for filtering based on the type.
     * @param string $startDate The start date for filtering applicants.
     * @param string $endDate The end date for filtering applicants.
     * @return float The average distance in kilometers.
     */
    private function calculateAverageDistance(string $type, ?int $id, string $startDate, string $endDate): float
    {
        $placedApplicants = $this->fetchPlacedApplicants($type, $id, $startDate, $endDate);

        $totalDistance = 0;
        $placedCount = $placedApplicants->count();

        foreach ($placedApplicants as $applicant) {
            $applicantCoords = $this->splitCoordinates($applicant->applicant_coordinates);
            $storeCoords = $this->splitCoordinates($applicant->store_coordinates);

            $distance = $this->haversineGreatCircleDistance(
                $applicantCoords['lat'],
                $applicantCoords['long'],
                $storeCoords['lat'],
                $storeCoords['long']
            );

            $totalDistance += $distance;
        }

        $averageDistance = ($placedCount > 0) ? ($totalDistance / $placedCount) : 0;

        return round($averageDistance, 2);
        ;
    }

    /**
     * Fetch placed applicants along with their coordinates and the store's coordinates.
     *
     * @param string $type The type of view (e.g., national, division, area, store).
     * @param int|null $id The ID for filtering based on the type.
     * @param string $startDate The start date for filtering applicants.
     * @param string $endDate The end date for filtering applicants.
     * @return \Illuminate\Support\Collection The collection of placed applicants.
     */
    private function fetchPlacedApplicants(string $type, ?int $id, string $startDate, string $endDate)
    {
        $query = DB::table('vacancy_fills')
            ->join('applicants', 'vacancy_fills.applicant_id', '=', 'applicants.id')
            ->join('vacancies', 'vacancy_fills.vacancy_id', '=', 'vacancies.id')
            ->join('stores', 'vacancies.store_id', '=', 'stores.id')
            ->select(
                'applicants.coordinates as applicant_coordinates',
                'stores.coordinates as store_coordinates'
            )
            ->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]);

        if ($type === 'division') {
            $query->where('stores.division_id', $id);
        } elseif ($type === 'region') {
            $query->where('stores.region_id', $id);
        } elseif ($type === 'store') {
            $query->where('stores.id', $id);
        }

        return $query->get();
    }

    /**
     * Split coordinates string into latitude and longitude.
     *
     * @param string $coordinates The coordinates string (e.g., "lat,long").
     * @return array An associative array with 'lat' and 'long'.
     */
    private function splitCoordinates(string $coordinates): array
    {
        $coords = explode(',', $coordinates);
        return [
            'lat' => floatval($coords[0]),
            'long' => floatval($coords[1]),
        ];
    }

    /**
     * Calculate the distance between two geographic coordinates using the Haversine formula.
     *
     * @param float $lat1 Latitude of the first point.
     * @param float $lon1 Longitude of the first point.
     * @param float $lat2 Latitude of the second point.
     * @param float $lon2 Longitude of the second point.
     * @param float $earthRadius The radius of the Earth in kilometers (default is 6371).
     * @return float The distance in kilometers.
     */
    private function haversineGreatCircleDistance(float $lat1, float $lon1, float $lat2, float $lon2, float $earthRadius = 6371): float
    {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) * sin($dlat / 2) +
            cos($lat1) * cos($lat2) *
            sin($dlon / 2) * sin($dlon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
