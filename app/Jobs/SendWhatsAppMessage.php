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
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $applicant;
    protected $message;
    protected $template;

    public function __construct(Applicant $applicant, $message, $template)
    {
        $this->applicant = $applicant;
        $this->message = $message;
        $this->template = $template;
    }

    public function handle()
    {
        $client = new GuzzleClient();
        $to = $this->applicant->phone;
        $from = config('services.meta.phone');
        $token = config('services.meta.token');

        $messageData = [
            [
                'message' => $this->message,
                'template' => $this->template
            ]
        ];

        $chatService = app(ChatService::class); // Resolve ChatService from the container
        $chatService->sendAndLogMessages($this->applicant, $messageData, $client, $to, $from, $token);
    }
}