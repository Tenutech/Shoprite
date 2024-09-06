<?php

use App\Jobs\UpdateApplicantData;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Notification;
use App\Models\User;
use App\Models\Vacancy;
use App\Services\VacancyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->vacancyService = new VacancyService();
    Queue::fake();
    Auth::loginUsingId(1);
});

it('sends regret notifications to unselected interviewed applicants', function () {

    $selectedApplicantIds = [2, 3]; 
    $unselectedApplicant = Applicant::factory()->create();
    $vacancy = Vacancy::factory()->create();
    $user = User::factory()->create();

    Interview::factory()->create([
        'applicant_id' => $unselectedApplicant->id,
        'interviewer_id' => $user->id,
        'vacancy_id' => $vacancy->id
    ]);

    $this->vacancyService->sendRegretInterviewedApplicants($selectedApplicantIds, 1);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $unselectedApplicant->id,
        'causer_id' => auth()->id(),
        'notification' => 'Has been declined 🚫',
        'read' => 'No',
    ]);
});

it('retrieves all interviewed applicants that should be regretted', function () {
    $selectedApplicantIds = [2, 3]; 
    $interviewedApplicant = Applicant::factory()->create();
    $vacancy = Vacancy::factory()->create();
    $user = User::factory()->create();

    Interview::factory()->create([
        'applicant_id' => $interviewedApplicant->id,
        'interviewer_id' => $user->id,
        'vacancy_id' => $vacancy->id
    ]);

    $applicantsToRegret = $this->vacancyService->getInterviewedApplicants($selectedApplicantIds, 1);
    
    expect($applicantsToRegret)->toHaveCount(1);
    expect($applicantsToRegret[0]->id)->toBe($interviewedApplicant->id);
});

it('sends regret notification to a single applicant', function () {
    $unselectedApplicant = Applicant::factory()->create();
    $vacancy = Vacancy::factory()->create();
    $user = User::factory()->create();

    $this->vacancyService->sendRegretNotification($unselectedApplicant, 1);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $unselectedApplicant->id,
        'causer_id' => auth()->id(),
        'notification' => 'Has been declined 🚫',
        'read' => 'No',
    ]);
});