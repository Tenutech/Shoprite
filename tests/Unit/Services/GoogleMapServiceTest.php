<?php

use App\Services\GoogleMapsService;
use App\Models\Town;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Mockery;
use function Pest\Laravel\mock;
use function Pest\Laravel\assertDatabaseHas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    Town::factory()->create(['name' => 'Cape Town', 'id' => 1]);
});

it('geocodes an address successfully', function () {
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

    $mockClient = Mockery::mock(Client::class);
    $mockClient->shouldReceive('makeRequest')->with('geocode/json', [
        "address" => "123 Main St"
    ])->andReturn($mockedResponse);

    $service = new GoogleMapsService();
    $service->setClient($mockClient);

    $result = $service->geocodeAddress('123 Main St');
    dd($result);
    expect($result)->toBeArray()
        ->and($result['formatted_address'])->toBe('123 Main St, Cape Town, South Africa')
        ->and($result['latitude'])->toBe(-33.9249)
        ->and($result['longitude'])->toBe(18.4241)
        ->and($result['city'])->toBe(1);

    assertDatabaseHas('towns', ['name' => 'Cape Town']);
});

it('returns null on API failure', function () {

    $mockedResponse = new Response(200, [], json_encode([
        'status' => 'ZERO_RESULTS',
        'results' => []
    ]));

    $mockClient = Mockery::mock(Client::class);
    $mockClient->shouldReceive('get')->andReturn($mockedResponse);

    $service = new GoogleMapsService();
    $service->setClient($mockClient);

    $result = $service->geocodeAddress('Invalid Address');

    expect($result)->toBeNull();
});

it('logs an error when API call fails', function () {

    $mockClient = Mockery::mock(Client::class);
    $mockClient->shouldReceive('get')->andThrow(new \Exception('API Error'));

    $service = new GoogleMapsService();
    $service->setClient($mockClient);

    Log::spy();

    $result = $service->geocodeAddress('123 Main St');

    expect($result)->toBeNull();
    Log::shouldHaveReceived('error')->with('Geocoding API error: API Error');
});