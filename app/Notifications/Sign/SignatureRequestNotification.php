<?php

namespace App\Notifications\Sign;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SignatureFile;
use App\Models\Signer;

class SignatureRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $signatureFile;
    protected $signer;

    public function __construct(SignatureFile $signatureFile, Signer $signer)
    {
        $this->signatureFile = $signatureFile;
        $this->signer = $signer;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Signature Request')
            ->greeting('Hello,')
            ->line('You have been requested to sign a document.')
            ->action('Sign Document', url('/sign/' . $this->signatureFile->id . '/' . $this->signer->id))
            ->line('Thank you for using our service!');
    }
}