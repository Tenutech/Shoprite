<?php

use App\Models\User;
use App\Models\Applicant;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

it('retrieves the correct applicants for the authenticated user', function () {
    // Create a user and set them as the authenticated user
    $user = User::factory()->create();
    Auth::login($user);

    // Create applicants with relationships
    $applicant1 = Applicant::factory()->create([
        'firstname' => 'John',
        'lastname' => 'Doe',
    ]);

    $applicant2 = Applicant::factory()->create([
        'firstname' => 'Jane',
        'lastname' => 'Doe',
    ]);

    // Attach the applicants to the user
    $user->savedApplicants()->attach([$applicant1->id, $applicant2->id]);

    // Call the controller action
    $response = $this->getJson(route('applicants.index'));

    // Assert the response status is OK
    $response->assertStatus(200);

    // Assert the JSON structure and content
    $response->assertJsonStructure([
        'applicants' => [
            '*' => [
                'firstname',
                'lastname',
                'encrypted_id',
                // Add other fields as necessary
            ],
        ],
    ]);

    // Assert that the applicants are in the correct order
    $response->assertJson([
        'applicants' => [
            [
                'firstname' => 'Jane',
                'lastname' => 'Doe',
                'encrypted_id' => Crypt::encryptString($applicant2->id),
            ],
            [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'encrypted_id' => Crypt::encryptString($applicant1->id),
            ],
        ],
    ]);
});