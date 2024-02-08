<?php

namespace App\Notifications;

use App\Models\Email;
use App\Models\EmailTemplate;
use App\Models\NotificationSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class NotifyEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $notification;

    /**
     * Create a new notification instance.
     */
    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Check user's settings to determine if they've opted in for email notifications
        $userSettings = NotificationSetting::where('user_id', $notifiable->id)->first();
        if ($userSettings && $userSettings->receive_email_notifications) {
            return ['mail'];
        }
        
        return [];
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
                'greeting' => 'Dear ' . $this->notification->user->firstname . ' ' . $this->notification->user->lastname,
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
        switch ($this->notification->notification) {
            case 'Submitted application 🔔':
                $this->submittedApplicationData();

                // Check if $templateID is set
                if (isset($this->templateID)) {
                    $template = EmailTemplate::findOrFail($this->templateID); // Proceed only if $templateID is set
                    $this->subject = $template->subject;
                    $this->introLines = explode(';;', $template->intro);
                }
                break;
            case 'Approved your application request ✅':
                $this->approvedApplicationData();

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
                    }
                    unset($line);
                }
                break;
            case 'Declined your application request 🚫':
                $this->declinedApplicationData();

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
                    }
                    unset($line);
                }
                break;
            case 'Interview Scheduled 📅':
                    $this->interviewScheduledData();
    
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
                            if (isset($this->date)) {
                                $line = str_replace('[Date]', $this->date, $line);
                            }
                            if (isset($this->start)) {
                                $line = str_replace('[Start Time]', $this->start, $line);
                            }
                            if (isset($this->store)) {
                                $line = str_replace('[Store]', $this->store, $line);
                            }
                            if (isset($this->location)) {
                                $line = str_replace('[Location]', $this->location, $line);
                            }
                            if (isset($this->notes)) {
                                $line = str_replace('[Notes]', $this->notes, $line);
                            }
                        }
                        unset($line);
                    }
                    break;
            case 'Confirmed your interview request ✅':
                $this->interviewConfirmedData();

                // Check if $templateID is set
                if (isset($this->templateID)) {
                    $template = EmailTemplate::findOrFail($this->templateID); // Proceed only if $templateID is set
                    $this->subject = $template->subject;
                    $this->introLines = explode(';;', $template->intro);

                    // Iterate through each line and replace placeholders as needed
                    foreach ($this->introLines as &$line) {
                        if (isset($this->userName)) {
                            $line = str_replace('[Applicant Name]', $this->userName, $line);
                        }
                        if (isset($this->vacancy)) {
                            $line = str_replace('[Position]', $this->vacancy, $line);
                        }
                        if (isset($this->date)) {
                            $line = str_replace('[Date]', $this->date, $line);
                        }
                        if (isset($this->start)) {
                            $line = str_replace('[Start Time]', $this->start, $line);
                        }
                        if (isset($this->store)) {
                            $line = str_replace('[Store]', $this->store, $line);
                        }
                        if (isset($this->location)) {
                            $line = str_replace('[Location]', $this->location, $line);
                        }
                    }
                    unset($line);
                }
                break;
            case 'Declined your interview request 🚫':
                $this->interviewDeclinedData();

                // Check if $templateID is set
                if (isset($this->templateID)) {
                    $template = EmailTemplate::findOrFail($this->templateID); // Proceed only if $templateID is set
                    $this->subject = $template->subject;
                    $this->introLines = explode(';;', $template->intro);

                    // Iterate through each line and replace placeholders as needed
                    foreach ($this->introLines as &$line) {
                        if (isset($this->userName)) {
                            $line = str_replace('[Applicant Name]', $this->userName, $line);
                        }
                        if (isset($this->vacancy)) {
                            $line = str_replace('[Position]', $this->vacancy, $line);
                        }
                        if (isset($this->date)) {
                            $line = str_replace('[Date]', $this->date, $line);
                        }
                        if (isset($this->start)) {
                            $line = str_replace('[Start Time]', $this->start, $line);
                        }
                        if (isset($this->store)) {
                            $line = str_replace('[Store]', $this->store, $line);
                        }
                        if (isset($this->location)) {
                            $line = str_replace('[Location]', $this->location, $line);
                        }
                    }
                    unset($line);
                }
                break;
            case 'Completed your interview 🚀':
                $this->interviewCompletedData();

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
                    }
                    unset($line);
                }
                break;
            case 'You have been Appointed 🎉':
                $this->applicantAppointedData();

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
                        if (isset($this->store)) {
                            $line = str_replace('[Store]', $this->store, $line);
                        }
                    }
                    unset($line);
                }
                break;
            case 'Has been declined 🚫':
                $this->applicantRegretData();

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
                        if (isset($this->store)) {
                            $line = str_replace('[Store]', $this->store, $line);
                        }
                    }
                    unset($line);
                }
                break;
            case 'Has applied for vacancy 🔔':
                $this->appliedForVacancyData();

                // Check if $templateID is set
                if (isset($this->templateID)) {
                    $template = EmailTemplate::findOrFail($this->templateID); // Proceed only if $templateID is set
                    $this->subject = $template->subject;
                    $this->introLines = explode(';;', $template->intro);

                    // Iterate through each line and replace placeholders as needed
                    foreach ($this->introLines as &$line) {                        
                        if (isset($this->userName)) {
                            $line = str_replace('[Applicant Name]', $this->userName, $line);
                        }
                        if (isset($this->vacancy)) {
                            $line = str_replace('[Position]', $this->vacancy, $line);
                        }
                        if (isset($this->userName)) {
                            $line = str_replace('[Date]', $this->applyDate, $line);
                        }
                    }
                    unset($line);
                }
                break;
            default:
                return false;
        }
    }

    /**
    * Set application submit data.
    */
    private function submittedApplicationData()
    {
        // Proceed with setting up the notification details
        $this->templateID = 3;
        $this->actionText = 'View Application';
        $this->actionUrl = route('profile.index');
        $this->userName = (optional($this->notification->causer)->firstname ?? 'N/A') . ' ' . (optional($this->notification->causer)->lastname ?? 'N/A');
        $this->outroText = optional(optional($this->notification->causer->applicant)->position)->name ?? 'N/A';
        $this->icon = \Illuminate\Support\Facades\URL::asset('images/' . (optional($this->notification->causer)->avatar ?? 'avatar.jpg'));
    }

    /**
    * Set application approved data.
    */
    private function approvedApplicationData()
    {
        // Proceed with setting up the notification details
        $this->templateID = 4;
        $this->actionText = 'View Application';
        $this->actionUrl = route('profile.index');
        $this->userName = (optional($this->notification->user)->firstname ?? 'N/A') . ' ' . (optional($this->notification->user)->lastname ?? 'N/A');
        $this->outroText = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->icon = \Illuminate\Support\Facades\URL::asset('images/' . (optional($this->notification->user)->avatar ?? 'avatar.jpg'));
        $this->vacancy = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
    }

    /**
    * Set application declined data.
    */
    private function declinedApplicationData()
    {
        // Proceed with setting up the notification details
        $this->templateID = 5;
        $this->actionText = 'View Application';
        $this->actionUrl = route('profile.index');
        $this->userName = (optional($this->notification->user)->firstname ?? 'N/A') . ' ' . (optional($this->notification->user)->lastname ?? 'N/A');
        $this->outroText = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->icon = \Illuminate\Support\Facades\URL::asset('images/' . (optional($this->notification->user)->avatar ?? 'avatar.jpg'));
        $this->vacancy = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
    }

    /**
    * Set interview scheduled data.
    */
    private function interviewScheduledData()
    {
        // Proceed with setting up the notification details
        $this->templateID = 7;
        $this->actionText = 'View Interview';
        $this->actionUrl = route('profile.index');
        $this->userName = (optional($this->notification->user)->firstname ?? 'N/A') . ' ' . (optional($this->notification->user)->lastname ?? 'N/A');
        $this->outroText = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->icon = \Illuminate\Support\Facades\URL::asset('images/' . (optional($this->notification->user)->avatar ?? 'avatar.jpg'));
        $this->vacancy = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->store = (optional(optional(optional(optional($this->notification->subject)->vacancy)->store)->brand)->name ?? 'N/A') . ' (' . (optional(optional(optional(optional($this->notification->subject)->vacancy)->store)->town)->name ?? 'N/A') . ')';
        $this->location = optional($this->notification->subject)->location ?? 'N/A';
        $this->notes = optional($this->notification->subject)->notes ?? 'N/A';

        // Format the date
        $scheduledDate = optional($this->notification->subject)->scheduled_date;
        if ($scheduledDate) {
            $dateObject = new \DateTime($scheduledDate);
            $this->date = $dateObject->format('d M Y');
        } else {
            $this->date = 'N/A';
        }

        // Format the start time
        $startTime = optional($this->notification->subject)->start_time;
        if ($startTime) {
            $timeObject = new \DateTime($startTime);
            $this->start = $timeObject->format('ha');
        } else {
            $this->start = 'N/A';
        }
    }

    /**
    * Set interview confirmed data.
    */
    private function interviewConfirmedData()
    {
        // Proceed with setting up the notification details
        $this->templateID = 8;
        $this->actionText = 'View Interview';
        $this->actionUrl = route('profile.index');
        $this->userName = (optional($this->notification->causer)->firstname ?? 'N/A') . ' ' . (optional($this->notification->causer)->lastname ?? 'N/A');
        $this->outroText = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->icon = \Illuminate\Support\Facades\URL::asset('images/' . (optional($this->notification->causer)->avatar ?? 'avatar.jpg'));
        $this->vacancy = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->store = (optional(optional(optional(optional($this->notification->subject)->vacancy)->store)->brand)->name ?? 'N/A') . ' (' . (optional(optional(optional(optional($this->notification->subject)->vacancy)->store)->town)->name ?? 'N/A') . ')';
        $this->location = optional($this->notification->subject)->location ?? 'N/A';

        // Format the date
        $scheduledDate = optional($this->notification->subject)->scheduled_date;
        if ($scheduledDate) {
            $dateObject = new \DateTime($scheduledDate);
            $this->date = $dateObject->format('d M Y');
        } else {
            $this->date = 'N/A';
        }

        // Format the start time
        $startTime = optional($this->notification->subject)->start_time;
        if ($startTime) {
            $timeObject = new \DateTime($startTime);
            $this->start = $timeObject->format('ha');
        } else {
            $this->start = 'N/A';
        }
    }

    /**
    * Set interview declined data.
    */
    private function interviewDeclinedData()
    {
        // Proceed with setting up the notification details
        $this->templateID = 9;
        $this->actionText = 'View Interview';
        $this->actionUrl = route('profile.index');
        $this->userName = (optional($this->notification->causer)->firstname ?? 'N/A') . ' ' . (optional($this->notification->causer)->lastname ?? 'N/A');
        $this->outroText = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->icon = \Illuminate\Support\Facades\URL::asset('images/' . (optional($this->notification->causer)->avatar ?? 'avatar.jpg'));
        $this->vacancy = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->store = (optional(optional(optional(optional($this->notification->subject)->vacancy)->store)->brand)->name ?? 'N/A') . ' (' . (optional(optional(optional(optional($this->notification->subject)->vacancy)->store)->town)->name ?? 'N/A') . ')';
        $this->location = optional($this->notification->subject)->location ?? 'N/A';

        // Format the date
        $scheduledDate = optional($this->notification->subject)->scheduled_date;
        if ($scheduledDate) {
            $dateObject = new \DateTime($scheduledDate);
            $this->date = $dateObject->format('d M Y');
        } else {
            $this->date = 'N/A';
        }

        // Format the start time
        $startTime = optional($this->notification->subject)->start_time;
        if ($startTime) {
            $timeObject = new \DateTime($startTime);
            $this->start = $timeObject->format('ha');
        } else {
            $this->start = 'N/A';
        }
    }

    /**
    * Set interview completed data.
    */
    private function interviewCompletedData()
    {
        // Proceed with setting up the notification details
        $this->templateID = 12;
        $this->actionText = 'View Profile';
        $this->actionUrl = route('profile.index');
        $this->userName = (optional($this->notification->user)->firstname ?? 'N/A') . ' ' . (optional($this->notification->user)->lastname ?? 'N/A');
        $this->outroText = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->icon = \Illuminate\Support\Facades\URL::asset('images/' . (optional($this->notification->user)->avatar ?? 'avatar.jpg'));
        $this->vacancy = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->store = (optional(optional(optional(optional($this->notification->subject)->vacancy)->store)->brand)->name ?? 'N/A') . ' (' . (optional(optional(optional(optional($this->notification->subject)->vacancy)->store)->town)->name ?? 'N/A') . ')';
        $this->location = optional($this->notification->subject)->location ?? 'N/A';

        // Format the date
        $scheduledDate = optional($this->notification->subject)->scheduled_date;
        if ($scheduledDate) {
            $dateObject = new \DateTime($scheduledDate);
            $this->date = $dateObject->format('d M Y');
        } else {
            $this->date = 'N/A';
        }

        // Format the start time
        $startTime = optional($this->notification->subject)->start_time;
        if ($startTime) {
            $timeObject = new \DateTime($startTime);
            $this->start = $timeObject->format('ha');
        } else {
            $this->start = 'N/A';
        }
    }

    /**
    * Set applicant appointed data.
    */
    private function applicantAppointedData()
    {
        // Proceed with setting up the notification details
        $this->templateID = 13;
        $this->actionText = 'View Profile';
        $this->actionUrl = route('profile.index');
        $this->userName = (optional($this->notification->user)->firstname ?? 'N/A') . ' ' . (optional($this->notification->user)->lastname ?? 'N/A');
        $this->outroText = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->icon = \Illuminate\Support\Facades\URL::asset('images/' . (optional($this->notification->user)->avatar ?? 'avatar.jpg'));
        $this->vacancy = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->store = (optional(optional(optional(optional($this->notification->subject)->vacancy)->store)->brand)->name ?? 'N/A') . ' (' . (optional(optional(optional(optional($this->notification->subject)->vacancy)->store)->town)->name ?? 'N/A') . ')';
    }

    /**
    * Set applicant regret data.
    */
    private function applicantRegretData()
    {
        // Proceed with setting up the notification details
        $this->templateID = 14;
        $this->actionText = 'View Profile';
        $this->actionUrl = route('profile.index');
        $this->userName = (optional($this->notification->user)->firstname ?? 'N/A') . ' ' . (optional($this->notification->user)->lastname ?? 'N/A');
        $this->outroText = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->icon = \Illuminate\Support\Facades\URL::asset('images/' . (optional($this->notification->user)->avatar ?? 'avatar.jpg'));
        $this->vacancy = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->store = (optional(optional(optional(optional($this->notification->subject)->vacancy)->store)->brand)->name ?? 'N/A') . ' (' . (optional(optional(optional(optional($this->notification->subject)->vacancy)->store)->town)->name ?? 'N/A') . ')';
    }

    /**
    * Set applied for vacancy data.
    */
    private function appliedForVacancyData()
    {
        // Proceed with setting up the notification details
        $this->templateID = 19;
        $this->actionText = 'View Applicat';
        $this->actionUrl = route('applicant-profile.index', ['id' => Crypt::encryptString($this->notification->causer->applicant->id)]);
        $this->userName = (optional($this->notification->causer)->firstname ?? 'N/A') . ' ' . (optional($this->notification->causer)->lastname ?? 'N/A');
        $this->outroText = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $this->icon = \Illuminate\Support\Facades\URL::asset('images/' . (optional($this->notification->causer)->avatar ?? 'avatar.jpg'));
        $this->vacancy = optional(optional(optional($this->notification->subject)->vacancy)->position)->name ?? 'N/A';
        $created_at = strtotime($this->notification->subject->created_at);
        $this->applyDate = $created_at ? date('d M Y', $created_at) : 'N/A';
    }
}