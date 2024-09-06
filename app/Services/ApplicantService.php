<?php

namespace App\Services;

use App\Models\Applicant;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegretEmail;

class ApplicantService
{
    /**
     * Send the no show notification to an applicant and dispatch an event.
     *
     * @param Applicant $applicant The applicant to send the regret notification to.
     * @return void
     */
    public function sendRegretNotification(Applicant $applicant): void
    {
        $notification = new Notification();
        $notification->user_id = $applicant->id;
        $notification->causer_id = Auth::id();
        $notification->subject()->associate($applicant);
        $notification->type_id = 1;
        $notification->notification = "You have missed too many interviews and have been removed from our talent pool";
        $notification->read = "No";
        $notification->save();

        //UpdateApplicantData::dispatch($applicant->id, 'updated', 'Rejected', $vacancyId)->onQueue('default');
    }
}
