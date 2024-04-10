<?php

namespace App\Jobs;

use App\Services\ChatService;
use App\Models\Applicant;
use Twilio\Rest\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $applicant;
    protected $message;

    public function __construct(Applicant $applicant, $message)
    {
        $this->applicant = $applicant;
        $this->message = $message;
    }

    public function handle()
    {
        $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
        $to = 'whatsapp:' . $this->applicant->phone;
        $from = config('services.twilio.whatsapp_number');
        $service = config('services.twilio.service_sid');

        $chatService = app(ChatService::class); // Resolve ChatService from the container
        $chatService->sendAndLogMessages($this->applicant, [$this->message], $twilio, $to, $from, $service);
    }
}