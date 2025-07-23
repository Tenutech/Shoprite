<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\ChatTotalData;
use App\Models\ChatMonthlyData;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogChatMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $applicantID, $message, $type, $messageId, $status, $template;

    /**
     * Create a new job instance.
     */
    public function __construct($applicantID, $message = null, $type = null, $messageId = null, $status = null, $template = null)
    {
        $this->applicantID = $applicantID;
        $this->message = $message;
        $this->type = $type;
        $this->messageId = $messageId;
        $this->status = $status;
        $this->template = $template;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // Create a new chat entry with the provided message data
            $chat = new Chat([
                'applicant_id' => $this->applicantID,
                'message' => $this->message,
                'type_id' => $this->type,
                'message_id' => $this->messageId,
                'status' => $this->status,
                'template' => $this->template
            ]);
            $chat->save();

            $currentYear = Carbon::now()->year;
            $currentMonth = strtolower(Carbon::now()->format('M'));

            // Determine the fields to update based on the message type
            $totalField = $this->type == 1 ? 'total_incoming' : 'total_outgoing';
            $monthField = $this->type == 1 ? $currentMonth . '_incoming' : $currentMonth . '_outgoing';

            $yearlyData = ChatTotalData::firstOrCreate(
                ['year' => $currentYear]
            );

            // Increment the total and monthly counters
            $yearlyData->increment($totalField);
            $yearlyData->increment($monthField);

            // Find or create ChatMonthlyData entry
            $monthlyData = ChatMonthlyData::firstOrCreate(
                [
                    'chat_total_data_id' => $yearlyData->id,
                    'chat_type' => $this->type == 1 ? 'Incoming' : 'Outgoing',
                    'month' => ucwords($currentMonth)
                ],
                ['count' => 1] // Initial count value
            );

            // Increment the count
            $monthlyData->increment('count');

            // Save the ChatMonthlyData entry
            $monthlyData->save();
        } catch (Exception $e) {
            //Log::error("Error in LogChatMessageJob: {$e->getMessage()}");
            throw new Exception('There was an error logging the message in the queue.');
        }
    }
}
