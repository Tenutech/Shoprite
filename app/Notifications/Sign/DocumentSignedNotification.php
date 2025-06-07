<?php

namespace App\Notifications\Sign;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SignatureFile;

class DocumentSignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $signatureFile;

    public function __construct(SignatureFile $signatureFile)
    {
        $this->signatureFile = $signatureFile;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Document Signed')
            ->greeting('Hello,')
            ->line('Your document has been successfully signed.')
            ->action('View Document', url('/signature-files/' . $this->signatureFile->id))
            ->line('Thank you for using our service!');
    }
}