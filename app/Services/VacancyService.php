<?php

namespace App\Services;

use App\Jobs\UpdateApplicantData;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class VacancyService
{
    /**
     * Send the regret notification to applicants who have been interviewed.
     *
     * @param array $selectedApplicantIds The list of applicant IDs that have been selected.
     * @param int $vacancyId The ID of the vacancy being processed.
     * @return void
     */
    public function sendRegretInterviewedApplicants(array $selectedApplicantIds, int $vacancyId): void
    {
        $applicantsToProcess = $this->getInterviewedApplicants($selectedApplicantIds, $vacancyId);
        foreach ($applicantsToProcess as $applicant) {
            $this->sendRegretNotification($applicant, $vacancyId);
        }
    }

    /**
     * Retrieve all applicants that should receive a regret notification.
     *
     * @param array $selectedApplicantIds The list of applicant IDs that have been selected.
     * @param int $vacancyId The ID of the vacancy being processed.
     * @return array The list of applicants to be regretted.
     */
    public function getInterviewedApplicants(array $selectedApplicantIds, int $vacancyId): array
    {
        $interviews = Interview::where('vacancy_id', $vacancyId)->get();

        $interviewedApplicants = [];
        foreach ($interviews as $interview) {
            $applicant = $interview->applicant;
            if (in_array($applicant->id, $selectedApplicantIds)) {
                continue;
            }

            $interviewedApplicants[] = $applicant;
        }
        return $interviewedApplicants;
    }

    /**
     * Send the regret notification to an applicant and dispatch an event.
     *
     * @param Applicant $applicant The applicant to send the regret notification to.
     * @param int $vacancyId The ID of the vacancy being processed.
     * @return void
     */
    public function sendRegretNotification(Applicant $applicant, int $vacancyId): void
    {
        $notification = new Notification();
        $notification->user_id = $applicant->id;
        $notification->causer_id = Auth::id();
        $notification->subject()->associate($applicant);
        $notification->type_id = 1;
        $notification->notification = "Has been declined 🚫";
        $notification->read = "No";
        $notification->save();

        UpdateApplicantData::dispatch($applicant->id, 'updated', 'Rejected', $vacancyId)->onQueue('default');
    }
}
