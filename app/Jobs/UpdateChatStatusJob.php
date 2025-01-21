<?php

namespace App\Jobs;

use App\Models\Chat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateChatStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The unique identifier of the message whose status is being updated.
     *
     * @var string
     */
    public $messageId;

    /**
     * The new status of the message (e.g., sent, delivered, failed).
     *
     * @var string
     */
    public $status;

    /**
     * Additional data associated with the status update.
     *
     * @var array
     */
    public $statusData;

    /**
     * Create a new job instance.
     *
     * @param string $messageId The unique identifier of the message.
     * @param string $status The new status of the message.
     * @param array $statusData Additional data from the webhook.
     * @return void
     */
    public function __construct($messageId, $status, $statusData)
    {
        // Initialize the message ID, status, and additional data.
        $this->messageId = $messageId;
        $this->status = $status;
        $this->statusData = $statusData;
    }

    /**
     * Execute the job to update the chat message status in the database.
     *
     * This method retrieves the chat record associated with the provided
     * message ID. If the record exists, it updates the status. For failed
     * statuses, it also logs the error code and reason.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->status === 'failed') {
            // Directly update the record with failed status details
            Chat::where('message_id', $this->messageId)->update([
                'status' => 'Failed',
                'code' => $this->statusData['errors'][0]['code'] ?? null,
                'reason' => $this->statusData['errors'][0]['title'] ?? 'Unknown reason',
            ]);
        } else {
            // Directly update the record for other statuses
            Chat::where('message_id', $this->messageId)->update([
                'status' => ucfirst($this->status),
            ]);
        }
    }
}
