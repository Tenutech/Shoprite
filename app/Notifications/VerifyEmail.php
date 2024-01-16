<?php

namespace App\Notifications;

use App\Models\Email;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;

class VerifyEmail extends VerifyEmailBase
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $template = EmailTemplate::findorfail(2);

        return (new MailMessage)
            ->subject($template->subject)
            ->view('vendor.notifications.verify', [
                'greeting' => $template->greeting,
                'introLines' => $template->intro,
                'actionText' => 'Verify Email',
                'actionUrl' => $this->verificationUrl($notifiable),
                'displayableActionUrl' => url($this->verificationUrl($notifiable))
            ]);
    }
}
