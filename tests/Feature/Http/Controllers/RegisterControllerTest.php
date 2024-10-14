<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Jobs\ProcessUserIdNumber;
use App\Models\Applicant;
use App\Models\Company;
use App\Models\Consent;
use App\Models\NotificationSetting;
use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a new user and handles various aspects of registration', function () {
    Queue::fake();
    Storage::fake('public');
    
    $company = Company::factory()->create();
    $role = Role::factory()->create(['id' => 7, 'name' => 'User']);
    $status = Status::factory()->create(['id' => 1, 'name' => 'Online']);

    $data = [
        'firstname' => 'John',
        'lastname' => 'Register',
        'email' => 'johnregister@example.com',
        'phone' => '1234567890',
        'id_number' => '8112045070088',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'guardian_mobile' => '0987654321',
        'company_id' => $company->id,
    ];

    Applicant::factory()->create(['id_number' => '9409035129083']);

    $response = $this->post(route('register'), $data);
  
    $this->assertDatabaseHas('users', [
        'firstname' => 'John',
        'lastname' => 'Register',
        'email' => 'johnregister@example.com',
        'phone' => '1234567890',
        //'address' => '123 Fake Street',
    ]);

    $user = User::where('email', 'johnregister@example.com')->first();
    $this->assertDatabaseHas('notification_settings', [
        'user_id' => $user->id,
    ]);

    // $age = app(RegisterController::class)->calculateAgeFromId($data['id_number']);
    // if ($age < 18) {
    //     $this->assertDatabaseHas('consents', [
    //         'user_id' => $user->id,
    //         'guardian_mobile' => '0987654321',
    //         'consent_status' => 'Pending',
    //     ]);
    // }
    
    // Queue::assertPushed(ProcessUserIdNumber::class, function ($job) use ($user) {
    //     return $job->user->id === $user->id;
    // });

    if (isset($data['avatar'])) {
        Storage::disk('public')->assertExists('/images/' . $user->avatar);
    } else {
        $this->assertEquals('avatar.jpg', $user->avatar);
    }
});