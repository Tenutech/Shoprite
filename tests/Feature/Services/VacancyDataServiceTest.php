<?php

use App\Models\Division;
use App\Models\Role;
use App\Models\Shortlist;
use App\Models\Store;
use App\Models\Region;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyFill;
use App\Services\DataService\VacancyDataService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('migrate:fresh');
});

it('calculates the nationwide average time to shortlist', function () {
    $service = new VacancyDataService();

    $vacancy1 = Vacancy::factory()->create(['created_at' => now()->subDays(5)]);
    $shortlist1 = Shortlist::factory()->create(['vacancy_id' => $vacancy1->id, 'created_at' => now()->subDays(2)]);

    $vacancy2 = Vacancy::factory()->create(['created_at' => now()->subDays(10)]);
    $shortlist2 = Shortlist::factory()->create(['vacancy_id' => $vacancy2->id, 'created_at' => now()->subDays(1)]);

    $averageTime = $service->getNationwideAverageTimeToShortlist();
   
    $expectedAverage = 6;
 
    expect($averageTime)->toBeGreaterThanOrEqual($expectedAverage - 0.1);
    expect($averageTime)->toBeLessThanOrEqual($expectedAverage + 0.1);
});

it('calculates the nationwide average time to hire', function () {
    $vacancy1 = Vacancy::factory()->create(['created_at' => now()->subDays(10)]);
    VacancyFill::factory()->create(['vacancy_id' => $vacancy1->id, 'created_at' => now()->subDays(5)]);

    $vacancy2 = Vacancy::factory()->create(['created_at' => now()->subDays(20)]);
    VacancyFill::factory()->create(['vacancy_id' => $vacancy2->id, 'created_at' => now()->subDays(10)]);

    $vacancyDataService = app(VacancyDataService::class);
    $nationwideAverageTimeToHire = $vacancyDataService->getNationwideAverageTimeToHire();

    $expectedAverage = 8;

    expect($nationwideAverageTimeToHire)->toBeGreaterThanOrEqual($expectedAverage - 0.1);
    expect($nationwideAverageTimeToHire)->toBeLessThanOrEqual($expectedAverage + 0.1);
});

it('calculates the nationwide average time to hire within a date range', function () {
    $vacancy1 = Vacancy::factory()->create(['created_at' => now()->subDays(30)]);
    VacancyFill::factory()->create(['vacancy_id' => $vacancy1->id, 'created_at' => now()->subDays(25)]);

    $vacancy2 = Vacancy::factory()->create(['created_at' => now()->subDays(20)]);
    VacancyFill::factory()->create(['vacancy_id' => $vacancy2->id, 'created_at' => now()->subDays(15)]);

    $vacancy3 = Vacancy::factory()->create(['created_at' => now()->subDays(10)]);
    VacancyFill::factory()->create(['vacancy_id' => $vacancy3->id, 'created_at' => now()->subDays(2)]);

    $startDate = Carbon::now()->subDays(20);
    $endDate = Carbon::now();

    $vacancyDataService = app(VacancyDataService::class);
    $nationwideAverageTimeToHire = $vacancyDataService->getNationwideAverageTimeToHire($startDate, $endDate);

    $expectedAverage = 7.0;

    expect($nationwideAverageTimeToHire)->toBeGreaterThanOrEqual($expectedAverage - 0.1);
    expect($nationwideAverageTimeToHire)->toBeLessThanOrEqual($expectedAverage + 0.1);
});

it('it calculates the store-specific average time to shortlist', function () {

    $startDate = Carbon::now()->subDays(20);
    $endDate = Carbon::now();

    $store = Store::factory()->create(['id' => 1]);
    $user = User::factory()->create(['id' => 1]);
   
    $vacancy1 = Vacancy::factory()->create(['store_id' => 1, 'created_at' => now()->subDays(15)]);
    $shortlist1 = Shortlist::factory()->create([
        'vacancy_id' => $vacancy1->id,
        'created_at' => now()->subDays(2)]
    );

    $vacancy2 = Vacancy::factory()->create(['store_id' => 1, 'created_at' => now()->subDays(20)]);
    $shortlist2 = Shortlist::factory()->create([
        'vacancy_id' => $vacancy1->id, 
        'created_at' => now()->subDays(3)]
    );

    $vacancyDataService = app(VacancyDataService::class);
    $storeAverage = $vacancyDataService->getStoreAverageTimeToShortlist(1, $startDate, $endDate);
  
    $expectedAverage = '25D 0H 0M';

    expect($storeAverage)->toBe($expectedAverage);
});

it('calculates the store-specific average time to shortlist', function () {

    $startDate = Carbon::now()->subDays(20);
    $endDate = Carbon::now();     

    $store = Store::factory()->create(['id' => 1]);
    User::factory()->create(['id' => 1]);

    $vacancy1 = Vacancy::factory()->create(['store_id' => 1, 'created_at' => now()->subDays(15)]);
    Shortlist::factory()->create(['vacancy_id' => $vacancy1->id, 'created_at' => now()->subDays(2)]);

    $vacancy2 = Vacancy::factory()->create(['store_id' => 1, 'created_at' => now()->subDays(20)]);
    Shortlist::factory()->create(['vacancy_id' => $vacancy2->id, 'created_at' => now()->subDays(3)]);

    $vacancyDataService = app(VacancyDataService::class);
    $storeAverage = $vacancyDataService->getStoreAverageTimeToShortlist(1, $startDate, $endDate);

    $expectedAverage = '30D 0H 0M';

    expect($storeAverage)->toBe($expectedAverage);
});
