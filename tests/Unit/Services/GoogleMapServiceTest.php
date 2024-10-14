<?php

use App\Services\GoogleMapsService;
use App\Models\Town;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Mockery as M;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Town::factory()->create(['name' => 'Cape Town', 'id' => 1]);
});

it('geocodes an address successfully', function () {
    // Mocking the Guzzle response
    $mockedResponse = new Response(200, [], json_encode([
        'status' => 'OK',
        'results' => [
            [
                'formatted_address' => '123 Main St, Cape Town, South Africa',
                'geometry' => [
                    'location' => [
                        'lat' => -33.9249,
                        'lng' => 18.4241,
                    ],
                ],
                'address_components' => [
                    [
                        'long_name' => 'Cape Town',
                        'types' => ['locality']
                    ]
                ]
            ]
        ]
    ]));

    // Mocking GuzzleHttp\Client and the get() method
    $mockClient = M::mock(Client::class);
    $mockClient->shouldReceive('get')
        ->with('geocode/json', M::on(function ($params) {
            return isset($params['query']['address']) && $params['query']['address'] === '123 Main St';
        }))
        ->andReturn($mockedResponse);

    // Set the mocked client in the GoogleMapsService
    $service = new GoogleMapsService();
    $service->setClient($mockClient);

    // Call the method to test
    $result = $service->geocodeAddress('123 Main St');

    expect($result)->toBeArray()
        ->and($result['formatted_address'])->toBe('123 Main St, Cape Town, South Africa')
        ->and($result['latitude'])->toBe(-33.9249)
        ->and($result['longitude'])->toBe(18.4241)
        ->and($result['city'])->toBe(1);

    // assertDatabaseHas for checking town in the database
    assertDatabaseHas('towns', ['name' => 'Cape Town']);
});

it('returns null on API failure', function () {
    // Mocking the failure response from Guzzle
    $mockedResponse = new Response(200, [], json_encode([
        'status' => 'ZERO_RESULTS',
        'results' => []
    ]));

    // Mocking the Guzzle client
    $mockClient = M::mock(Client::class);
    $mockClient->shouldReceive('get')->andReturn($mockedResponse);

    // Set the mocked client in the service
    $service = new GoogleMapsService();
    $service->setClient($mockClient);

    // Test the service returns null on failure
    $result = $service->geocodeAddress('Invalid Address');

    expect($result)->toBeNull();
});

it('logs an error when API call fails', function () {
    // Mocking a failure for the API call
    $mockClient = M::mock(Client::class);
    $mockClient->shouldReceive('get')->andThrow(new \Exception('API Error'));

    // Set the mocked client in the service
    $service = new GoogleMapsService();
    $service->setClient($mockClient);

    // Spy on the Log facade
    Log::spy();

    // Make the call that should fail
    $result = $service->geocodeAddress('123 Main St');

    // Expect the result to be null due to the failure
    expect($result)->toBeNull();

    // Ensure the error was logged correctly
    Log::shouldHaveReceived('error')->with('Geocoding API error: API Error');
});