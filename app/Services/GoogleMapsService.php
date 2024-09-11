<?php

namespace App\Services;

use App\Models\Town;
use App\Models\Province;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.googlemaps.key');
        $this->client = new Client(['base_uri' => 'https://maps.googleapis.com/maps/api/']);
    }

    public function geocodeAddress($address)
    {
        return $this->makeRequest('geocode/json', ['address' => $address]);
    }

    /**
     * Get the HTTP client instance.
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set the HTTP client instance.
     *
     * @param \GuzzleHttp\Client $client
     * @return void
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function reverseGeocodeCoordinates($latitude, $longitude)
    {
        return $this->makeRequest('geocode/json', ['latlng' => "{$latitude},{$longitude}"]);
    }

    private function makeRequest($endpoint, $parameters)
    {
        try {
            $parameters['key'] = $this->apiKey;
            $response = $this->client->get($endpoint, ['query' => $parameters]);
            $data = json_decode($response->getBody(), true);

            if ($data['status'] === 'OK') {
                $result = $data['results'][0];

                // Extract the city from address_components
                $city = null;
                $provinceId = null;

                //Loop through each address component
                foreach ($result['address_components'] as $component) {
                    // Check if the component contains a 'locality' type, which usually refers to the city or town name
                    if (in_array('locality', $component['types'])) {
                        // Convert the city name to lowercase and then capitalize each word (e.g., "new york" -> "New York")
                        $city = ucwords(strtolower($component['long_name']));
                    }

                    // Check if the component contains 'administrative_area_level_1', which typically refers to the province/state
                    if (in_array('administrative_area_level_1', $component['types'])) {
                        // Search for the province in the database by matching the name (partial match using LIKE to handle variations)
                        $province = Province::where('name', 'LIKE', '%' . $component['long_name'] . '%')->first();
                        $provinceId = $province ? $province->id : null;
                    }

                    // Once both the city and the province ID have been found, we can stop looping through the address components
                    if ($city && $provinceId) {
                        break;
                    }
                }

                // Check if city exists in towns table
                $town = Town::where('name', $city)->first();

                // If the town does not exist, insert it into the towns table
                if (!$town) {
                    $town = Town::create([
                        'name' => $city,
                        'province_id' => $provinceId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $city = $town ? $town->id : null;
               
                return [
                    'formatted_address' => $result['formatted_address'],
                    'latitude' => $result['geometry']['location']['lat'],
                    'longitude' => $result['geometry']['location']['lng'],
                    'city' => $city  // add the extracted city here
                ];
            }

            Log::warning('Geocoding API returned status: ' . $data['status']);
            return null;
        } catch (\Exception $e) {
            Log::error('Geocoding API error: ' . $e->getMessage());
            return null;
        }
    }
}
