<?php

namespace App\Notifications;

use App\Models\Email;
use App\Models\EmailTemplate;
use App\Models\NotificationSetting;
use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class ReminderEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reminderType;
    protected $items;
    protected $lastItem;

    /**
     * Create a new notification instance.
     */
    public function __construct($reminderType, Collection $items)
    {
        $this->reminderType = $reminderType;
        $this->items = $items;
        $this->lastItem = $items->last();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Default to not sending the email
        $sendMail = false;

        // Check user's settings to determine if they've opted in for email notifications
        $userSettings = NotificationSetting::where('user_id', $notifiable->id)->first();

        if ($userSettings && $userSettings->receive_email_notifications) {
            // Determine if the last item is a vacancy or a shortlist
            if (isset($this->lastItem->open_positions)) {
                // It's a vacancy, directly use open_positions
                $sendMail = $this->lastItem->open_positions > 0;
            } elseif (isset($this->lastItem->vacancy->open_positions)) {
                // It's a shortlist, access open_positions through the related vacancy
                $sendMail = $this->lastItem->vacancy->open_positions > 0;
            }
        }

        return $sendMail ? ['mail'] : [];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $this->prepareMailData();

        return (new MailMessage)
            ->subject($this->subject)
            ->view('vendor.notifications.notification', [
                'greeting' => 'Dear ' . $this->lastItem->user->firstname . ' ' . $this->lastItem->user->lastname,
                'introLines' => $this->introLines,
                'actionText' => $this->actionText,
                'actionUrl' => $this->actionUrl,
                'userName' => $this->userName,
                'outroText' => $this->outroText,
                'icon' => $this->icon,
                'displayableActionUrl' => url('/'),
            ]);
    }

    /**
     * Prepare the data for the email.
     */
    private function prepareMailData()
    {
        switch ($this->reminderType->type) {
            case 'vacancy_created_no_shortlist':
                $this->vacancyCreatedNoShortlistData();

                // Check if $templateID is set
                if (isset($this->templateID)) {
                    $template = EmailTemplate::findOrFail($this->templateID); // Proceed only if $templateID is set
                    $this->subject = $template->subject;
                    $this->introLines = explode(';;', $template->intro);

                    // Iterate through each line and replace placeholders as needed
                    foreach ($this->introLines as &$line) {
                        if (isset($this->vacancy)) {
                            $line = str_replace('[Position]', $this->vacancy, $line);
                        }
                        if (isset($this->open)) {
                            $line = str_replace('[Open]', $this->open, $line);
                        }
                        if (isset($this->store)) {
                            $line = str_replace('[Store]', $this->store, $line);
                        }
                        if (isset($this->type)) {
                            $line = str_replace('[Type]', $this->type, $line);
                        }
                    }
                    unset($line);
                }
                break;
            case 'shortlist_created_no_interview':
                $this->shortlistCreatedNoInterviewData();

                // Check if $templateID is set
                if (isset($this->templateID)) {
                    $template = EmailTemplate::findOrFail($this->templateID); // Proceed only if $templateID is set
                    $this->subject = $template->subject;
                    $this->introLines = explode(';;', $template->intro);

                    // Iterate through each line and replace placeholders as needed
                    foreach ($this->introLines as &$line) {
                        if (isset($this->vacancy)) {
                            $line = str_replace('[Position]', $this->vacancy, $line);
                        }
                        if (isset($this->open)) {
                            $line = str_replace('[Open]', $this->open, $line);
                        }
                        if (isset($this->store)) {
                            $line = str_replace('[Store]', $this->store, $line);
                        }
                        if (isset($this->type)) {
                            $line = str_replace('[Type]', $this->type, $line);
                        }
                        if (isset($this->candidates)) {
                            $line = str_replace('[Candidates]', $this->candidates, $line);
                        }
                    }
                    unset($line);
                }
                break;
            case 'interview_scheduled_no_vacancy_filled':
                $this->interviewScheduledNoVacancyFilledData();

                // Check if $templateID is set
                if (isset($this->templateID)) {
                    $template = EmailTemplate::findOrFail($this->templateID); // Proceed only if $templateID is set
                    $this->subject = $template->subject;
                    $this->introLines = explode(';;', $template->intro);

                    // Iterate through each line and replace placeholders as needed
                    foreach ($this->introLines as &$line) {
                        if (isset($this->vacancy)) {
                            $line = str_replace('[Position]', $this->vacancy, $line);
                        }
                        if (isset($this->open)) {
                            $line = str_replace('[Open]', $this->open, $line);
                        }
                        if (isset($this->store)) {
                            $line = str_replace('[Store]', $this->store, $line);
                        }
                        if (isset($this->type)) {
                            $line = str_replace('[Type]', $this->type, $line);
                        }
                        if (isset($this->candidates)) {
                            $line = str_replace('[Candidates]', $this->candidates, $line);
                        }
                    }
                    unset($line);
                }
                break;
        }
    }

    /**
    * Set no interview after shortlist data.
    */
    private function vacancyCreatedNoShortlistData()
    {
        // Proceed with setting up the notification details
        $this->templateID = 20;
        $this->actionText = 'View Vacancy';
        $this->actionUrl = route('job-overview.index', ['id' => Crypt::encryptString($this->lastItem->id)]);
        $this->userName = (optional($this->lastItem->user)->firstname ?? 'N/A') . ' ' . (optional($this->lastItem->user)->lastname ?? 'N/A');
        $this->outroText = optional($this->lastItem->position)->name ?? 'N/A';
        $this->icon = \Illuminate\Support\Facades\URL::asset((optional($this->lastItem->position)->image ?? 'build/images/logo.png'));
        $this->vacancy = optional($this->lastItem->position)->name ?? 'N/A';
        $this->open = $this->lastItem->open_positions ?? 'N/A';
        $this->store = (optional(optional($this->lastItem->store)->brand)->name ?? 'N/A') . ' (' . (optional(optional($this->lastItem->store)->town)->name ?? 'N/A') . ')';
        $this->type = optional($this->lastItem->type)->name ?? 'N/A';
    }

    /**
    * Set vacancy created no shortlist data.
    */
    private function shortlistCreatedNoInterviewData()
    {
        // Proceed with setting up the notification details
        $this->templateID = 21;
        $this->actionText = 'View Shortlist';
        $this->actionUrl = route('shortlist.index', ['id' => Crypt::encryptString($this->lastItem->vacancy_id)]);
        $this->userName = (optional($this->lastItem->user)->firstname ?? 'N/A') . ' ' . (optional($this->lastItem->user)->lastname ?? 'N/A');
        $this->outroText = optional(optional($this->lastItem->vacancy)->position)->name ?? 'N/A';
        $this->icon = \Illuminate\Support\Facades\URL::asset((optional(optional($this->lastItem->vacancy)->position)->image ?? 'build/images/logo.png'));
        $this->vacancy = optional(optional($this->lastItem->vacancy)->position)->name ?? 'N/A';
        $this->open = optional($this->lastItem->vacancy)->open_positions ?? 'N/A';
        $this->store = (optional(optional(optional($this->lastItem->vacancy)->store)->brand)->name ?? 'N/A') . ' (' . (optional(optional(optional($this->lastItem->vacancy)->store)->town)->name ?? 'N/A') . ')';
        $this->type = optional(optional($this->lastItem->vacancy)->type)->name ?? 'N/A';
        // If applicant_ids is not null, decode it and count the number of applicants
        if (!is_null($this->lastItem->applicant_ids)) {
            $applicantIds = json_decode($this->lastItem->applicant_ids, true);
            $this->candidates = is_array($applicantIds) ? count($applicantIds) : 0;
        } else {
            $this->candidates = 0;
        }
    }

    /**
    * Set interview scheduled no vacancy filled data.
    */
    private function interviewScheduledNoVacancyFilledData()
    {
        // Proceed with setting up the notification details
        $this->templateID = 22;
        $this->actionText = 'View Shortlist';
        $this->actionUrl = route('shortlist.index', ['id' => Crypt::encryptString($this->lastItem->vacancy_id)]);
        $this->userName = (optional($this->lastItem->user)->firstname ?? 'N/A') . ' ' . (optional($this->lastItem->user)->lastname ?? 'N/A');
        $this->outroText = optional(optional($this->lastItem->vacancy)->position)->name ?? 'N/A';
        $this->icon = \Illuminate\Support\Facades\URL::asset((optional(optional($this->lastItem->vacancy)->position)->image ?? 'build/images/logo.png'));
        $this->vacancy = optional(optional($this->lastItem->vacancy)->position)->name ?? 'N/A';
        $this->open = optional($this->lastItem->vacancy)->open_positions ?? 'N/A';
        $this->store = (optional(optional(optional($this->lastItem->vacancy)->store)->brand)->name ?? 'N/A') . ' (' . (optional(optional(optional($this->lastItem->vacancy)->store)->town)->name ?? 'N/A') . ')';
        $this->type = optional(optional($this->lastItem->vacancy)->type)->name ?? 'N/A';
        // If applicant_ids is not null, decode it and count the number of applicants
        if (!is_null($this->lastItem->applicant_ids)) {
            $applicantIds = json_decode($this->lastItem->applicant_ids, true);
            $this->candidates = is_array($applicantIds) ? count($applicantIds) : 0;
        } else {
            $this->candidates = 0;
        }
    }

    /**
     * Log the reminder record after sending the notification.
     *
     * @param  mixed  $user  The notifiable entity, usually a User model.
     */
    public function afterSend($user)
    {
        try {
            Reminder::create([
                'user_id' => $user->id,
                'reminder_setting_id' => $this->reminderType->id, // Ensure this is the correct way to access the ID
                'email_template_id' => $this->reminderType->email_template_id , // Make sure this property is set
            ]);

        } catch (\Exception $e) {
            // Log error or handle exception
            Log::error("Error logging reminder: {$e->getMessage()}");
        }
    }
}
