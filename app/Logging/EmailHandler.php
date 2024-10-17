<?php

namespace App\Logging;

use Monolog\Handler\MailHandler;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;
use App\Notifications\ErrorEmail;
use Illuminate\Support\Facades\Notification;

class EmailHandler extends MailHandler
{
    protected $to;
    protected $subject;

    public function __construct($level = Logger::ERROR, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->to = config('logging.channels.email.to');
        $this->subject = config('logging.channels.email.subject');
    }

    protected function send($content, array $records): void
    {
        $record = $records[0]; // Take the first record to get the message and context

        $this->actionUrl = route('home');
        $this->userName = Auth::check() ? Auth::user()->firstname . ' ' . Auth::user()->lastname : 'N/A';

        // Format the file and line information
        $fileAndLine = '';
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Exception) {
            $exception = $record['context']['exception'];
            $fileAndLine = sprintf('at %s:%d', $exception->getFile(), $exception->getLine());
        }

        // Prepare data for the view (customize as needed)
        $data = [
            'greeting' => 'Error Notification',
            'introLines' => [
                'An error has occurred in the application:',
                $record['message'],
                $fileAndLine // Include the file and line information
            ],
            'actionText' => 'Shoprite - Job Opportunities',
            'actionUrl' => $this->actionUrl,
            'userName' => $this->userName,
            'company' => 'Shoprite',
        ];

        // Send the email using the rendered HTML content
        try {
            Notification::route('mail', $this->to)
                        ->notify(new ErrorEmail($data));
        } catch (\Exception $e) {
            // Handle mail sending failure, maybe log it
            \Log::error('Failed to send error notification email: ' . $e->getMessage());
        }
    }
}
