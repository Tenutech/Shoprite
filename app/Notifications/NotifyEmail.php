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
                'greeting' => 'Hey, ' . $this->notification->user->firstname . ' ' . $this->notification->user->lastname,
                'introLines' => $this->introLines,
                'actionText' => $this->actionText,
                'actionUrl' => $this->actionUrl,
                'userName' => $this->userName,
                'company' => $this->company,
                'opportunity' => $this->opportunity,
                'category' => $this->category,
                'icon' => $this->icon,
                'displayableActionUrl' => url('/'),
            ]);
    }

    /**
     * Prepare the data for the email.
     */
    private function prepareMailData()
    {
        if ($this->notification->subject_type == 'App\Models\Connection') {
            $this->setConnectionData();

            $template = EmailTemplate::findorfail($this->templateID); // Handle if not found.
            $this->subject = $template->subject;
            $this->introLines = explode(';;', $template->intro);

            // Insert $userName into the second position
            array_splice($this->introLines, 1, 0, $this->userName);

            // Replace any occurrences of [Sender Name] with $userName
            $this->introLines = array_map(function ($line) {
                return str_replace('[Sender Name]', $this->userName, $line);
            }, $this->introLines);
        } elseif ($this->notification->subject_type == 'App\Models\Opportunity') {
            $this->setOpportunityData();

            $template = EmailTemplate::findorfail($this->templateID);  // Handle if not found.
            $this->subject = $template->subject;
            $this->introLines = explode(';;', $template->intro);

            if ($this->templateID == 3) {
                $this->introLines[2] = str_replace('[Title]', $this->opportunity, $this->introLines[2]);
                $this->introLines[3] = str_replace('[Category]', $this->category, $this->introLines[3]);
            } else if ($this->templateID == 7) {
                $amendments = $this->notification->subject->amendments->last()?->description;
                if ($this->templateID && $amendments) {
                    array_splice($this->introLines, 2, 0, $amendments);
                }
            }
        }
    }
    
    /**
     * Set the connection data.
     */
    private function setConnectionData()
    {
        $this->actionText = 'Send Message';
        $this->actionUrl = route('messages.index', ['id' => Crypt::encryptString($this->notification->subject->user_id)]);
        $this->userName = $this->notification->causer->firstname.' '.$this->notification->causer->lastname;
        $this->company = $this->notification->causer->company->name;
        $this->icon = $this->notification->causer->avatar;
        $this->templateID = 4;
    }

    /**
     * Set the opportunity data.
     */
    private function setOpportunityData()
    {
        switch ($this->notification->notification) {
            case 'Has been approved ✅':
                $this->templateID = 5;
                break;
            case 'Needs amendment 📝':
                $this->templateID = 6;
                break;
            case 'Has been declined 🚫':
                $this->templateID = 7;
                break;
            case 'Created New Opportunity 🔔':
                $this->templateID = 3;
                break;
            default:
                $this->templateID = 1;
                break;
                
        }

        $this->actionText = 'View Opportunity';
        $this->actionUrl = route('opportunity-overview.index', ['id' => Crypt::encryptString($this->notification->subject_id)]);
        $this->opportunity = $this->notification->subject->name;
        $this->category = $this->notification->subject->category->name;
        $this->icon = $this->notification->subject->sectors[0]->image;
    }
}