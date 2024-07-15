<?php

namespace App\Services;

use Google_Client;
use Google_Service_MapsEngine;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.googlemaps.key');
        $this->client = new Google_Client();
        $this->client->setDeveloperKey($this->apiKey);
        $this->client->setApplicationName("Google Maps Service");
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
            $service = new Google_Service_MapsEngine($this->client);
            $url = "https://maps.googleapis.com/maps/api/{$endpoint}?" . http_build_query($parameters);
            
            $response = $this->client->execute($service->request('GET', $url));
            if ($response['status'] === 'OK') {
                $result = $response['results'][0];
                return [
                    'formatted_address' => $result['formatted_address'],
                    'latitude' => $result['geometry']['location']['lat'],
                    'longitude' => $result['geometry']['location']['lng'],
                ];
            }

            Log::warning('Geocoding API returned status: ' . $response['status']);
            return null;
        } catch (\Exception $e) {
            Log::error('Geocoding API error: ' . $e->getMessage());
            return null;
        }
    }
}