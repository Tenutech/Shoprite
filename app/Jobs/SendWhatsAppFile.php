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

class SendWhatsAppFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $applicant;
    protected $fileUrl;

    public function __construct(Applicant $applicant, $fileUrl)
    {
        $this->applicant = $applicant;
        $this->fileUrl = $fileUrl;
    }

    public function handle()
    {
        $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
        $to = 'whatsapp:' . $this->applicant->phone;
        $from = config('services.twilio.whatsapp_number');

        $twilio->messages->create($to, [
            'from' => $from,
            'mediaUrl' => [$this->fileUrl]
        ]);
    }
}