<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Vacancy;
use App\Models\Shortlist;
use App\Models\Interview;
use App\Models\Applicant;
use App\Models\Reminder;
use App\Models\ReminderSetting;
use Carbon\Carbon;
use App\Notifications\ReminderEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to relavant parities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->notifyNoShortlistAfterVacancy();
        $this->notifyNoInterviewAfterShortlist();
        $this->notifyNoAppointmentAfterInterview();
    }

    /*
    |--------------------------------------------------------------------------
    | No Shortlist After Vacancy
    |--------------------------------------------------------------------------
    */

    protected function notifyNoShortlistAfterVacancy()
    {
        //Reminder
        $reminderType = ReminderSetting::where('type', 'vacancy_created_no_shortlist')->first();

        // Check if the reminder setting is active
        if ($reminderType && $reminderType->is_active === 1) {
            $vacancies = Vacancy::where('created_at', '<=', Carbon::now()->subDays($reminderType->delay))
                                ->whereDoesntHave('shortlists')
                                ->get();

            // Group the vacancies by user for notification
            $vacanciesByUser = $vacancies->groupBy('user_id');

            foreach ($vacanciesByUser as $userId => $userVacancies) {
                $user = User::find($userId);
                if ($user) {
                    // Check if a reminder has already been sent within the delay period
                    $alreadySent = Reminder::where('user_id', $user->id)
                                           ->where('reminder_setting_id', $reminderType->id)
                                           ->where('created_at', '>=', Carbon::now()->subDays($reminderType->delay))
                                           ->exists();

                    if (!$alreadySent) {
                        // No reminder has been sent in the specified period, proceed to send
                        $reminderEmail = new ReminderEmail($reminderType, $userVacancies);
                        $user->notify($reminderEmail);
                        $reminderEmail->afterSend($user); // Log the reminder
                    }
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | No Interview After Shortlist
    |--------------------------------------------------------------------------
    */

    protected function notifyNoInterviewAfterShortlist()
    {
        //Reminder
        $reminderType = ReminderSetting::where('type', 'shortlist_created_no_interview')->first();

        // Check if the reminder setting is active
        if ($reminderType && $reminderType->is_active === 1) {
            $shortlists = Shortlist::where('created_at', '<=', Carbon::now()->subDays($reminderType->delay))->get();

            foreach ($shortlists as $shortlist) {
                // Check if applicant_ids is empty or if no interviews have been scheduled for any applicants
                if (empty(json_decode($shortlist->applicant_ids, true)) || !$this->anyApplicantHasScheduledInterview($shortlist)) {
                    $user = User::find($shortlist->user_id);
                    if ($user) {
                        // Check if a reminder has already been sent within the delay period
                        $alreadySent = Reminder::where('user_id', $user->id)
                                               ->where('reminder_setting_id', $reminderType->id)
                                               ->where('created_at', '>=', Carbon::now()->subDays($reminderType->delay))
                                               ->exists();

                        if (!$alreadySent) {
                            // No reminder has been sent in the specified period, proceed to send
                            $reminderEmail = new ReminderEmail($reminderType, collect([$shortlist]));
                            $user->notify($reminderEmail);
                            $reminderEmail->afterSend($user); // Log the reminder
                        }
                    }
                }
            }
        }
    }

    /**
     * Check if any applicant in the shortlist has a scheduled interview.
     *
     * @param  Shortlist  $shortlist
     * @return bool
     */
    protected function anyApplicantHasScheduledInterview(Shortlist $shortlist): bool
    {
        $applicantIds = json_decode($shortlist->applicant_ids, true);

        if (empty($applicantIds)) {
            return false;
        }

        foreach ($applicantIds as $applicantId) {
            $interviewExists = Interview::where('applicant_id', $applicantId)
                                        ->where('vacancy_id', $shortlist->vacancy_id)
                                        ->exists();
            if ($interviewExists) {
                // If any applicant has a scheduled interview, return true
                return true;
            }
        }

        // If no applicants have scheduled interviews, return false
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | No Appointment After Interview
    |--------------------------------------------------------------------------
    */

    protected function notifyNoAppointmentAfterInterview()
    {
        $reminderType = ReminderSetting::where('type', 'interview_scheduled_no_vacancy_filled')->first();

        if ($reminderType && $reminderType->is_active === 1) {
            $shortlists = Shortlist::where('created_at', '<=', Carbon::now()->subDays($reminderType->delay))
                                   ->whereNotNull('applicant_ids')
                                   ->where('applicant_ids', '<>', '[]')
                                   ->get();

            foreach ($shortlists as $shortlist) {
                if ($this->anyApplicantInterviewedButNotAppointed($shortlist)) {
                    $user = User::find($shortlist->user_id);
                    if ($user) {
                        // Check if a reminder has already been sent within the delay period
                        $alreadySent = Reminder::where('user_id', $user->id)
                                               ->where('reminder_setting_id', $reminderType->id)
                                               ->where('created_at', '>=', Carbon::now()->subDays($reminderType->delay))
                                               ->exists();

                        if (!$alreadySent) {
                            // No reminder has been sent in the specified period, proceed to send
                            $reminderEmail = new ReminderEmail($reminderType, collect([$shortlist]));
                            $user->notify($reminderEmail);
                            $reminderEmail->afterSend($user); // Log the reminder
                        }
                    }
                }
            }
        }
    }

    /**
     * Check if any applicant in the shortlist has been interviewed but not appointed.
     *
     * @param Shortlist $shortlist
     * @return bool
     */
    protected function anyApplicantInterviewedButNotAppointed(Shortlist $shortlist): bool
    {
        $applicantIds = json_decode($shortlist->applicant_ids, true);

        // Check if any of these applicants have been interviewed
        $interviewedApplicants = Interview::whereIn('applicant_id', $applicantIds)
                                          ->where('vacancy_id', $shortlist->vacancy_id)
                                          ->pluck('applicant_id');

        if ($interviewedApplicants->isEmpty()) {
            return false; // None of the applicants have been interviewed.
        }

        // Now, check if any of the interviewed applicants have not been appointed
        $appointedCount = Applicant::whereIn('id', $interviewedApplicants)
                                   ->whereNull('appointed_id')
                                   ->count();

        // If the count of appointed applicants is equal to the number of interviewed applicants, it means there are interviewed but not appointed applicants.
        return $appointedCount == count($interviewedApplicants);
    }
}
