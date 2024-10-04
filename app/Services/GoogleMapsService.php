<?php

namespace App\Services;

use App\Models\Town;
use App\Models\Province;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    protected $client; // The HTTP client used for making API requests
    protected $apiKey; // The Google Maps API key

    /**
     * Constructor to initialize the Google Maps API key and the HTTP client.
     */
    public function __construct()
    {
        // Fetch the API key from the services configuration file
        $this->apiKey = config('services.googlemaps.key');

        // Initialize Guzzle HTTP client with Google's base API URI
        $this->client = new Client(['base_uri' => 'https://maps.googleapis.com/maps/api/']);
    }

    /**
     * Geocode an address by sending a request to Google Maps Geocoding API.
     * 
     * @param string $address The address to geocode.
     * @return array|null The geocoded result or null if the request fails.
     */
    public function geocodeAddress($address)
    {
        // Make a request to geocode the address
        return $this->makeRequest('geocode/json', ['address' => $address]);
    }

    /**
     * Get the current HTTP client instance.
     * 
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set a custom HTTP client instance.
     * 
     * @param \GuzzleHttp\Client $client The HTTP client to set.
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Reverse geocode coordinates (latitude and longitude) by sending a request
     * to Google Maps Geocoding API.
     * 
     * @param float $latitude The latitude coordinate.
     * @param float $longitude The longitude coordinate.
     * @return array|null The reverse geocoded result or null if the request fails.
     */
    public function reverseGeocodeCoordinates($latitude, $longitude)
    {
        // Make a request to reverse geocode the coordinates
        return $this->makeRequest('geocode/json', ['latlng' => "{$latitude},{$longitude}"]);
    }

    /**
     * Send a request to the Google Maps API and process the response.
     * 
     * @param string $endpoint The API endpoint (e.g., 'geocode/json').
     * @param array $parameters The parameters to send with the request.
     * @return array|null The processed geocoded data or null if the request fails.
     */
    private function makeRequest($endpoint, $parameters)
    {
        try {
            // Add the API key to the request parameters
            $parameters['key'] = $this->apiKey;

            // Make the GET request to the Google Maps API
            $response = $this->client->get($endpoint, ['query' => $parameters]);

            // Decode the JSON response
            $data = json_decode($response->getBody(), true);

            // Check if the API request was successful
            if ($data['status'] === 'OK') {
                // Get the first result from the response
                $result = $data['results'][0];

                // Initialize variables for city, postal code, and province
                $city = null;
                $postalCode = null;
                $provinceId = null;
                $latitude = $result['geometry']['location']['lat'];
                $longitude = $result['geometry']['location']['lng'];

                // Loop through each component of the address to extract city and province
                foreach ($result['address_components'] as $component) {
                    // Check if the component is a 'locality' (i.e., a city or town)
                    if (in_array('locality', $component['types'])) {
                        // Capitalize each word in the city name (e.g., "new york" -> "New York")
                        $city = ucwords(strtolower($component['long_name']));
                    }

                    // Check if the component is a 'postal_code'
                    if (in_array('postal_code', $component['types'])) {
                        $postalCode = $component['long_name'];
                    }

                    // Check if the component is 'administrative_area_level_1' (a province/state)
                    if (in_array('administrative_area_level_1', $component['types'])) {
                        // Find the province in the database using a partial match
                        $province = Province::where('name', 'LIKE', '%' . $component['long_name'] . '%')->first();
                        $provinceId = $province ? $province->id : null;
                    }

                    // If both city and province are found, stop further looping
                    if ($city && $provinceId) {
                        break;
                    }
                }

                // If no province was found from the Google response, try to determine it by coordinates
                if (!$provinceId) {
                    $provinceId = $this->determineProvinceByCoordinates($latitude, $longitude);
                }

                // Check if the city exists in the towns table
                $town = Town::where('name', $city)->first();

                // If the town does not exist, create it in the database
                if (!$town) {
                    $town = Town::create([
                        'name' => $city,
                        'province_id' => $provinceId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Get the town's ID
                $city = $town ? $town->id : null;

                // Return the formatted address, coordinates, and city ID
                return [
                    'formatted_address' => $result['formatted_address'],
                    'latitude' => $result['geometry']['location']['lat'],
                    'longitude' => $result['geometry']['location']['lng'],
                    'city' => $city  // The city (town) ID in the database
                ];
            }

            // Log a warning if the geocoding request failed
            Log::warning('Geocoding API returned status: ' . $data['status']);
            return null;

        } catch (\Exception $e) {
            // Log the error message for debugging
            Log::error('Geocoding API error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Determine which province the given coordinates fall into by checking
     * against predefined provincial boundaries.
     * 
     * @param float $latitude The latitude of the coordinates.
     * @param float $longitude The longitude of the coordinates.
     * @return int|null The ID of the province in the database or null if not found.
     */
    private function determineProvinceByCoordinates($latitude, $longitude)
    {
        // Define the bounding box for each province in South Africa
        $provinceBounds = [
            'Eastern Cape' => [
                'southwest' => [-34.1966, 22.1520],
                'northeast' => [-29.0416, 30.7485]
            ],
            'Free State' => [
                'southwest' => [-30.7010, 24.3347],
                'northeast' => [-26.6186, 29.7467]
            ],
            'Gauteng' => [
                'southwest' => [-26.7056, 27.3072],
                'northeast' => [-25.3723, 28.8333]
            ],
            'KwaZulu-Natal' => [
                'southwest' => [-31.6167, 28.9500],
                'northeast' => [-26.8361, 32.8500]
            ],
            'Limpopo' => [
                'southwest' => [-25.7597, 25.2333],
                'northeast' => [-22.1256, 31.3833]
            ],
            'Mpumalanga' => [
                'southwest' => [-27.3167, 28.9900],
                'northeast' => [-24.0000, 32.0000]
            ],
            'Northern Cape' => [
                'southwest' => [-34.8333, 16.5000],
                'northeast' => [-26.0000, 24.0000]
            ],
            'North West' => [
                'southwest' => [-28.0250, 22.8333],
                'northeast' => [-24.6833, 28.1000]
            ],
            'Western Cape' => [
                'southwest' => [-34.8333, 17.9333],
                'northeast' => [-31.0000, 23.0000]
            ]
        ];

        // Iterate over the bounds of each province to see where the coordinates fall
        foreach ($provinceBounds as $province => $bounds) {
            // Check if the given latitude and longitude fall within the province's bounds
            if (
                $latitude >= $bounds['southwest'][0] &&
                $latitude <= $bounds['northeast'][0] &&
                $longitude >= $bounds['southwest'][1] &&
                $longitude <= $bounds['northeast'][1]
            ) {
                // If found, look up the province ID in the database
                $provinceModel = Province::where('name', 'LIKE', '%' . $province . '%')->first();
                return $provinceModel ? $provinceModel->id : null;
            }
        }

        // Return null if the coordinates do not fall within any known provincial bounds
        return null;
    }
}
