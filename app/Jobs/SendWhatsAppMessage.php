<?php

namespace App\Jobs;

use App\Services\ChatService;
use App\Models\Applicant;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // Define the protected properties for the job
    protected $applicant;
    protected $message;
    protected $type;
    protected $template;
    protected $variables;

    /**
     * Create a new job instance.
     *
     * @param Applicant $applicant The applicant to whom the message will be sent
     * @param string $message The message content
     * @param string $type The type of the message (default is 'template')
     * @param string|null $template The template name (optional)
     * @param array $variables The variables to be sent with the template
     */
    public function __construct(Applicant $applicant, $message, $type = 'template', $template = null, array $variables = [])
    {
        // Initialize the properties with the provided values
        $this->applicant = $applicant;
        $this->message = $message;
        $this->type = $type;
        $this->template = $template;
        $this->variables = $variables;
    }

    /**
     * Execute the job.
     *
     * This method sends a WhatsApp message to the applicant using the ChatService.
     */
    public function handle()
    {
        // Create a new Guzzle HTTP client instance
        $client = new GuzzleClient();

        // Retrieve the recipient's phone number from the applicant model
        $to = $this->applicant->phone;

        // Retrieve the sender's phone number and API token from the configuration
        $from = config('services.meta.phone');
        $token = config('services.meta.token');

        // Prepare the message data array
        $messageData = [
            [
                'message' => $this->message, // The message content
                'type' => $this->type, // The type of the message
                'template' => $this->template, // The template name
                'variables' => $this->variables // The variables array
            ]
        ];

        // Resolve the ChatService from the service container
        $chatService = app(ChatService::class);

        // Send and log the message using the ChatService
        $chatService->sendAndLogMessages($this->applicant, $messageData, $client, $to, $from, $token);
    }
}
