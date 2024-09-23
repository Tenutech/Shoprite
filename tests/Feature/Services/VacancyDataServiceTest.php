<?php

use App\Models\Vacancy;
use App\Models\Shortlist;
use App\Services\DataService\VacancyDataService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// it('calculates the nationwide average time to shortlist', function () {
//     $service = new VacancyDataService();

//     $vacancy1 = Vacancy::factory()->create(['created_at' => now()->subDays(5)]);
//     $shortlist1 = Shortlist::factory()->create(['vacancy_id' => $vacancy1->id, 'created_at' => now()->subDays(2)]);

//     $vacancy2 = Vacancy::factory()->create(['created_at' => now()->subDays(10)]);
//     $shortlist2 = Shortlist::factory()->create(['vacancy_id' => $vacancy2->id, 'created_at' => now()->subDays(1)]);

//     $averageTime = $service->getNationwideAverageTimeToShortlist();
   
//     $expectedAverage = 144.01;
 
//     expect($averageTime)->toBeGreaterThanOrEqual($expectedAverage - 0.1);
//     expect($averageTime)->toBeLessThanOrEqual($expectedAverage + 0.1);
// });

// test('it calculates the store-specific average time to shortlist', function () {
//     $vacancy1 = Vacancy::factory()->hasShortlists(1)->create([
//         'store_id' => 1,
//         'created_at' => now()->subDays(7),
//     ]);
//     $shortlist1 = $vacancy1->shortlists()->first();
//     $shortlist1->update(['created_at' => now()->subDays(3)]);

//     $vacancy2 = Vacancy::factory()->hasShortlists(1)->create([
//         'store_id' => 1,
//         'created_at' => now()->subDays(10),
//     ]);
//     $shortlist2 = $vacancy2->shortlists()->first();
//     $shortlist2->update(['created_at' => now()->subDays(5)]);

//     Vacancy::factory()->hasShortlists(1)->create([
//         'store_id' => 2,
//         'created_at' => now()->subDays(10),
//     ]);

//     $vacancyDataService = app(VacancyDataService::class);
//     $storeAverage = $vacancyDataService->getStoreAverageTimeToShortlist(1);

//     $expectedAverage = (4 + 5) / 2 * 24;

//     expect($storeAverage)->toBeGreaterThanOrEqual($expectedAverage - 0.1);
//     expect($storeAverage)->toBeLessThanOrEqual($expectedAverage + 0.1);
// });

// test('it calculates the time-filtered average time to shortlist', function () {

//     Vacancy::factory()->has(Shortlist::factory()->count(1))->create();

//     $vacancyDataService = app(VacancyDataService::class);

//     $currentMonthStart = Carbon::now()->startOfMonth();
//     $currentMonthEnd = Carbon::now()->endOfMonth();
//     $timeFilteredAverage = $vacancyDataService->getTimeFilteredAverageToShortlist($currentMonthStart, $currentMonthEnd);
    
//     expect($timeFilteredAverage)->toBeGreaterThan(0);
// });