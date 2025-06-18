<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class VerifyEmailUpdate extends Mailable
{
    // Enables queuing for mail delivery to improve performance
    use Queueable;
    use SerializesModels;

    /**
     * The user instance.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * Create a new message instance.
     *
     * This constructor method initializes the Mailable with the user's data.
     * It accepts a `User` model instance and assigns it to the `$user` property.
     *
     * @param \App\Models\User $user The user who is requesting an email verification.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the email message.
     *
     * This method constructs the email, generating a secure, signed URL
     * for email verification. The link expires after 15 minutes.
     *
     * @return $this
     */
    public function build()
    {
        // Generate a secure signed route URL for email verification
        $relativeUrl = URL::temporarySignedRoute(
            'verify.email.update', // The route name that will handle verification
            now()->addMinutes(15), // Set expiration time for the link (15 minutes)
            ['token' => $this->user->email_verification_token] // Include the token as a route parameter
        );

        // Ensure APP_URL is used to form the correct absolute URL
        $verificationUrl = config('app.url') . parse_url($relativeUrl, PHP_URL_PATH) . '?' . parse_url($relativeUrl, PHP_URL_QUERY);

        // Construct the email with a subject, view, and passing the verification URL
        return $this->from('noreply@otbgroup.co.za', 'Shoprite Job Opportunities')
                    ->subject('Verify Your Email for Profile Update')
                    ->view('vendor.notifications.verify-email-update')
                    ->with(['verificationUrl' => $verificationUrl]);
    }
}
