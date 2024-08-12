<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use App\Jobs\SendQueryToJira;
use App\Models\Query;
use App\Models\Status;
use App\Models\User;

class QueryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->status = Status::factory()->create();
        $this->user = User::factory()->create([
            'status_id' => $this->status->id,
        ]);

        $this->actingAs($this->user);
    }

    // public function test_index_returns_queries()
    // {  
    //     $queries = Query::factory()->count(3)->create(['user_id' => $this->user->id]);

    //     $response = $this->get('/queries');

    //     $response->assertStatus(200);

    //     foreach ($queries as $query) {
    //         $response->assertSee($query->subject);
    //     }
    // }

    public function test_store_creates_query()
    {
        Bus::fake();

        $response = $this->post('/queries/store', [
            'subject' => 'Test Query',
            'body' => 'This is a test for the query controller',
        ]);
    
        // Assert that the response status code is 200 (OK)
        $response->assertStatus(200);
    
        // Assert that the query is stored in the database
        $this->assertDatabaseHas('queries', [
            'subject' => 'Test Query',
            'body' => 'This is a test for the query controller',
            'user_id' => $this->user->id,
        ]);
    
        Assert that the SendQueryToJira job was dispatched
        Bus::assertDispatched(SendQueryToJira::class, function ($job) {
            return $job->getQuery()->subject === 'Test Query';
        });
    }
}