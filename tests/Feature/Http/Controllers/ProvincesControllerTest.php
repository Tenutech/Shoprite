<?php

use App\Models\Province;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
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

// it('creates a new province with valid data', function () {

//     $data = ['name' => 'Western Cape'];

//     // Call the controller action
//     $response = $this->postJson(route('province.store'), $data);

//     // Assert the response status is OK
//     $response->assertStatus(200)
//         ->assertJson(fn (AsserttableJson $json) =>
//             $json->where('success', true)
//                  ->where('message', 'Province created successfully!')
//     );

//     $this->assertDatabaseHas('provinces', ['name' => 'Western Cape']);

// });