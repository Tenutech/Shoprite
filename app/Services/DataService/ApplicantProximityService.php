<?php

namespace App\Services\DataService;

use Illuminate\Support\Facades\DB;

class ApplicantProximityService
{
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
            ->whereNull('applicants.appointed_id')
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

        return number_format($averageDistance, 2);
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
