<?php

use App\Models\Applicant;
use App\Models\State;
use App\Services\DataService\ApplicantDataService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('migrate:fresh');
});

// it('returns the correct application completion rate for the given date range', function () {

//     $completedState = State::factory()->create([
//         'id' => config('constants.complete_state_id'), 
//         'name' => 'Contact Number', 
//         'code' => 'contact_number'
//         ]
//     );
//     $otherState = State::factory()->create([
//         'id' => 7,
//         'name' => 'Scheduled', 
//         'code' => 'scheduled'
//         ]
//     );

//     $completedStateId = config('constants.complete_state_id');

//     Applicant::factory()->createMany([
//         ['state_id' => $completedStateId, 'created_at' => now()->subDays(10)],
//         ['state_id' => $completedStateId , 'created_at' => now()->subDays(5)], 
//         ['state_id' => $otherState, 'created_at' => now()->subDays(8)],
//     ]);

//     $startDate = now()->subMonth()->toDateString();
//     $endDate = now()->toDateString();

//     $service = new ApplicantDataService();
//     $completionRate = $service->getApplicationCompletionRate($startDate, $endDate);

//     expect($completionRate)->toBe('66.67');
// });

// it('returns 0 if there are no applicants', function () {

//     $startDate = now()->subMonth()->toDateString();
//     $endDate = now()->toDateString();

//     $service = new ApplicantDataService();
//     $completionRate = $service->getApplicationCompletionRate($startDate, $endDate);

//     expect($completionRate)->toBe('0.00');
// });

// it('returns the correct drop-off rates and percentages for the given date range', function () {

//     $completedState = State::factory()->create([
//         'id' => config('constants.complete_state_id'), 
//         'name' => 'Complete', 
//         'code' => 'complete'
//         ]
//     );
//     $stage1 = State::factory()->create([
//         'id' => 31, 
//         'name' => 'Stage1', 
//         'code' => 'stage1'
//         ]
//     );
//     $stage2 = State::factory()->create([
//         'id' => 32, 
//         'name' => 'Stage2', 
//         'code' => 'stage2'
//         ]
//     );

//     $completedStateId = config('constants.complete_state_id');;

//     Applicant::factory()->createMany([
//         ['state_id' => $stage1->id, 'created_at' => now()->subDays(15)],
//         ['state_id' => $stage1->id, 'created_at' => now()->subDays(17)],
//         ['state_id' => $stage2->id, 'created_at' => now()->subDays(10)],
//         ['state_id' => $stage2->id, 'created_at' => now()->subDays(5)], 
//         ['state_id' => $completedState->id, 'created_at' => now()->subDays(8)], 
//     ]);

//     $startDate = now()->subMonth(2)->toDateString();
//     $endDate = now()->toDateString();

//     $service = new ApplicantDataService();
//     $result = $service->getDropOffRates($startDate, $endDate);

//     expect($result['dropoff_rate']['count'])->toBe(4);
//     expect($result['dropoff_rate']['percentage'])->toBe('80.00');

//     expect($result['dropoff_by_stage']['stage1']['count'])->toBe(2);
//     expect($result['dropoff_by_stage']['stage1']['percentage'])->toBe(40.00);
//     expect($result['dropoff_by_stage']['stage2']['count'])->toBe(2);
//     expect($result['dropoff_by_stage']['stage2']['percentage'])->toBe(40.00);
// });

// it('returns 0 drop-off rate if no applicants are found', function () {
//     $startDate = now()->subMonth()->toDateString();
//     $endDate = now()->toDateString();

//     $service = new ApplicantDataService();
//     $result = $service->getDropOffRates($startDate, $endDate);

//     expect($result['dropoff_rate']['count'])->toBe(0);
//     expect($result['dropoff_rate']['percentage'])->toBe('0.00');

//     expect($result['dropoff_by_stage'])->toBeArray();
// });