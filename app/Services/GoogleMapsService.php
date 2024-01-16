<?php

namespace App\Services;

use App\Models\Town;
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
                foreach ($result['address_components'] as $component) {
                    if (in_array('locality', $component['types'])) {
                        $city = $component['long_name'];
                        break;
                    }
                }

                // Check if city exists in towns table
                $town = Town::where('name', $city)->first();
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