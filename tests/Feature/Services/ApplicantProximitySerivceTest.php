<?php

use App\Models\Applicant;
use App\Models\Division;
use App\Models\Region;
use App\Models\State;
use App\Models\Store;
use App\Services\DataService\ApplicantProximityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('migrate:fresh');
    State::create(['code' => 'complete']);
});

it('returns the correct number of applicants by month for division', function () {
 
    $type = 'division';
    $startDate = Carbon::create(2023, 1, 1);
    $endDate = Carbon::create(2023, 3, 31);
    $maxDistanceFromStore = 10;

    $division = Division::factory()->create([
        'name' => 'Northern Shoprite',
    ]);

    Store::factory()->create([
        'id' => 1,
        'division_id' => $division->id,
        'coordinates' => '37.7749,-122.4194'
    ]);

    Applicant::factory()->create([
        'created_at' => Carbon::create(2023, 1, 15),
        'state_id' => State::where('code', 'complete')->first()->id,
        'coordinates' => '37.7750,-122.4195'
    ]);

    Applicant::factory()->create([
        'created_at' => Carbon::create(2023, 2, 20),
        'state_id' => State::where('code', 'complete')->first()->id,
        'coordinates' => '37.7751,-122.4196'
    ]);

    Applicant::factory()->create([
        'created_at' => Carbon::create(2023, 3, 25),
        'state_id' => State::where('code', 'complete')->first()->id,
        'coordinates' => '37.7752,-122.4197'
    ]);

    $service = new ApplicantProximityService();
    $applicantsByMonth = $service->getDivisionTalentPoolApplicantsByMonth($division->id, $startDate, $endDate, $maxDistanceFromStore);
    
    expect($applicantsByMonth)->toEqual([
        'Jan' => 1,
        'Feb' => 1,
        'Mar' => 1,
    ]);
});

it('returns the correct number of applicants by month for region', function () {

    $type = 'region';
    $regionId = 2;
    $startDate = Carbon::create(2023, 1, 1);
    $endDate = Carbon::create(2023, 3, 31);
    $maxDistanceFromStore = 10;

    $region = Region::factory()->create([
        'name' => 'Checkers Hyper',
    ]);

    Store::factory()->create([
        'id' => 2,
        'region_id' => $region->id,
        'coordinates' => '37.7749,-122.4194'
    ]);

    Applicant::factory()->create([
        'created_at' => Carbon::create(2023, 1, 15),
        'state_id' => State::where('code', 'complete')->first()->id,
        'coordinates' => '37.7750,-122.4195'
    ]);

    Applicant::factory()->create([
        'created_at' => Carbon::create(2023, 2, 20),
        'state_id' => State::where('code', 'complete')->first()->id,
        'coordinates' => '37.7751,-122.4196'
    ]);

    Applicant::factory()->create([
        'created_at' => Carbon::create(2023, 3, 25),
        'state_id' => State::where('code', 'complete')->first()->id,
        'coordinates' => '37.7752,-122.4197'
    ]);

   $service = new ApplicantProximityService();
    $applicantsByMonth = $service->getRegionTalentPoolApplicantsByMonth($region->id, $startDate, $endDate, $maxDistanceFromStore);

    expect($applicantsByMonth)->toEqual([
        'Jan' => 1,
        'Feb' => 1,
        'Mar' => 1,
    ]);
});