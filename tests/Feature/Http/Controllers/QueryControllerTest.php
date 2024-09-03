<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Query;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\{get, post, actingAs};

uses(RefreshDatabase::class);

beforeEach(function () {

    $this->status = Status::factory()->create();
    $this->user = User::factory()->create([
        'status_id' => $this->status->id,
    ]);

    actingAs($this->user);
});

// it('creates a query', function () {
//     $response = post('/queries/store', [
//         'subject' => 'Test Query',
//         'body' => 'This is a test for the query controller',
//     ]);

//     $response->assertStatus(302);

//     expect('queries')->toHaveRecord([
//         'subject' => 'Test Query',
//         'body' => 'This is a test for the query controller',
//         'user_id' => $this->user->id,
//     ]);
// });

// it('returns queries in the index', function () {
//     $queries = Query::factory()->count(3)->create(['user_id' => $this->user->id]);

//     $response = get('/queries');

//     $response->assertStatus(200);

//     foreach ($queries as $query) {
//         $response->assertSee($query->subject);
//     }
// });
