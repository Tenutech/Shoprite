<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Support\Facades\Bus;
use App\Jobs\SendQueryToJira;
use App\Models\Query;
use App\Models\Status;
use App\Models\User;
use function Pest\Laravel\{actingAs, assertDatabaseHas, post};

beforeEach(function () {
    // Set up the database and user
    $this->status = Status::factory()->create();
    $this->user = User::factory()->create([
        'status_id' => $this->status->id,
    ]);

    actingAs($this->user);
});

it('stores a query and dispatches a job', function () {
    // Fake the Bus to test job dispatch
    Bus::fake();

    $response = post('/queries/store', [
        'subject' => 'Test Query',
        'body' => 'This is a test for the query controller',
    ]);

    // Assert that the response status code is 200 (OK)
    $response->assertStatus(200);

    // Assert that the query is stored in the database
    assertDatabaseHas('queries', [
        'subject' => 'Test Query',
        'body' => 'This is a test for the query controller',
        'user_id' => $this->user->id,
    ]);

    // Assert that the SendQueryToJira job was dispatched
    Bus::assertDispatched(SendQueryToJira::class, function ($job) {
        return $job->getQuery()->subject === 'Test Query';
    });
});
