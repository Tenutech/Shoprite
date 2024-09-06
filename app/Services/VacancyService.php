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
    public function sendRegretNotifications(array $selectedApplicantIds, int $vacancyId): void
    {
        $vacancy = Vacancy::find($vacancyId);

        $appliedByInterview = $this->getInterviewedApplicants($selectedApplicantIds, $vacancyId);
        $appliedDirectly = $this->getDirectApplicants($selectedApplicantIds, $vacancyId);
        $applicantsToProcess = array_merge($appliedByInterview, $appliedDirectly);

        foreach ($applicantsToProcess as $value) {
            $this->sendRegretNotification($value['applicant'], $vacancyId, $value['subject']);
        }
    }

     /**
     * Retrieve all applicants that applied directly to the vacancy
     *
     * @param array $selectedApplicantIds The list of applicant IDs that have been selected.
     * @param Vacancy $vacancy The vacancy being processed.
     * @return array The list of applicants to be regretted.
     */
    public function getDirectApplicants(array $selectedApplicantIds, Vacancy $vacancy): array
    {
        $applicants = [];
        $allApplicants = $vacancy->applicants;

        foreach ($allApplicants as $applicant) {
            if (in_array($applicant->id, $selectedApplicantIds)) {
                continue;
            }

            $applicants[$applicant->id]['applicant'] = $applicant;
            $applicants[$applicant->id]['subject'] = $applicant;
        }
        return $applicants;
    }

    /**
     * Retrieve all applicants that have interviews, they did not directly apply but were chosen
     * or shorlisted
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
            if (in_array($applicant->id, $selectedApplicantIds)) {
                continue;
            }

            $interviewedApplicants[$applicant->id]['applicant'] = $applicant;
            $interviewedApplicants[$applicant->id]['subject'] = $interview;
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
    public function sendRegretNotification(Applicant $applicant, int $vacancyId, $subject): void
    {
        $notification = new Notification();
        $notification->user_id = $applicant->id;
        $notification->causer_id = Auth::id();
        $notification->subject()->associate($subject);
        $notification->type_id = 1;
        $notification->notification = "Has been declined 🚫";
        $notification->read = "No";
        $notification->save();

        UpdateApplicantData::dispatch($applicant->id, 'updated', 'Rejected', $vacancyId)->onQueue('default');
    }
}
