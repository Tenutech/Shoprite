<?php

namespace App\Services;

use App\Jobs\UpdateApplicantData;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Notification;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Auth;

class VacancyService
{
    /**
     * Send regret notifications to applicants who have been interviewed or applied directly.
     *
     * @param array $selectedApplicantIds The list of applicant IDs that have been selected.
     * @param int $vacancyId The ID of the vacancy being processed.
     * @return void
     */
    public function sendRegretNotifications(array $selectedApplicantIds, int $vacancyId): void
    {
        $appliedByInterview = $this->getApplicantsByInterview($selectedApplicantIds, $vacancyId);
        $appliedDirectly = $this->getDirectApplicants($selectedApplicantIds, $vacancyId);

        // Merge and reindex the arrays to ensure proper iteration
        $applicantsToProcess = array_merge($appliedByInterview, $appliedDirectly);
        $applicantsToProcess = array_values($applicantsToProcess);

        foreach ($applicantsToProcess as $applicantData) {
            $this->sendRegretNotification($applicantData['applicant'], $vacancyId, $applicantData['subject']);
        }
    }

    /**
     * Retrieve all applicants that applied directly to the vacancy.
     *
     * @param array $selectedApplicantIds The list of applicant IDs that have been selected.
     * @param int $vacancyId The ID of the vacancy being processed.
     * @return array The list of applicants to be regretted.
     */
    public function getDirectApplicants(array $selectedApplicantIds, int $vacancyId): array
    {
        $vacancy = Vacancy::find($vacancyId);
        $allApplicants = $vacancy->applicants;

        $applicants = [];
        foreach ($allApplicants as $applicant) {
            if (!in_array($applicant->id, $selectedApplicantIds)) {
                $applicants[$applicant->id] = [
                    'applicant' => $applicant,
                    'subject' => $applicant
                ];
            }
        }

        return $applicants;
    }

    /**
     * Retrieve all applicants who have been interviewed but did not directly apply.
     *
     * @param array $selectedApplicantIds The list of applicant IDs that have been selected.
     * @param int $vacancyId The ID of the vacancy being processed.
     * @return array The list of applicants to be regretted.
     */
    public function getApplicantsByInterview(array $selectedApplicantIds, int $vacancyId): array
    {
        $interviews = Interview::where('vacancy_id', $vacancyId)->get();

        $interviewedApplicants = [];
        foreach ($interviews as $interview) {
            $applicant = $interview->applicant;
            if (!in_array($applicant->id, $selectedApplicantIds)) {
                $interviewedApplicants[$applicant->id] = [
                    'applicant' => $applicant,
                    'subject' => $interview
                ];
            }
        }

        return $interviewedApplicants;
    }

    /**
     * Send a regret notification to an applicant and dispatch an event.
     *
     * @param Applicant $applicant The applicant to send the regret notification to.
     * @param int $vacancyId The ID of the vacancy being processed.
     * @param mixed $subject The subject of the notification (Interview or Applicant).
     * @return void
     */
    public function sendRegretNotification(Applicant $applicant, int $vacancyId, $subject): void
    {
        Notification::create([
            'user_id' => $applicant->id,
            'causer_id' => Auth::id(),
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'type_id' => 1,
            'notification' => 'Has been declined 🚫',
            'read' => 'No',
        ]);

        UpdateApplicantData::dispatch($applicant->id, 'updated', 'Rejected', $vacancyId)
            ->onQueue('default');
    }
}
