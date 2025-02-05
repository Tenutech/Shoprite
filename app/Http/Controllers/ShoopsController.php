<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Chat;
use Illuminate\Http\Request;
use App\Services\ChatService;
use App\Jobs\UpdateChatStatusJob;
use App\Jobs\BatchUpdateChatStatusJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ShoopsController extends Controller
{
    /**
    * The ChatService instance.
    *
    * @var \App\Services\ChatService
    */
    protected $chatService;

    /**
    * Create a new ShoopsController instance.
    *
    * @param  \App\Services\ChatService  $chatService  The ChatService instance.
    * @return void
    */
    public function __construct(ChatService $chatService)
    {
        // Dependency injection of the ChatService, ensuring that the service
        // is provided to the controller when it is instantiated.
        $this->chatService = $chatService;
    }

    /*
    |--------------------------------------------------------------------------
    | Shoops Chat Bot
    |--------------------------------------------------------------------------
    */

    /**
    * Handle the incoming chat message from the user.
    *
    * @param  \Illuminate\Http\Request  $request  The incoming request containing the chat message and other data.
    * @return \Illuminate\Http\JsonResponse
    */
    public function shoops(Request $request)
    {
        try {
            $data = $request->json()->all();

            // Check if the incoming webhook is for a status update
            if (isset($data['entry'][0]['changes'][0]['value']['statuses'][0])) {
                // Handle status update
                //$this->handleStatusUpdate($data);
            } else {
                // Use the ChatService to process the incoming message
                $this->chatService->handleIncomingMessage($data);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in shoops method: ' . $e->getMessage());
        }

        // Respond to the request indicating the message has been received and processed
        return response()->json(['message' => 'Received'], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Status Update
    |--------------------------------------------------------------------------
    */

    /**
     * Handle the status update of a previously sent message.
     *
     * This method processes incoming status updates from the WhatsApp webhook,
     * updating the message status in the `chats` table based on the `message_id`.
     * For failed messages, it stores the error code and reason.
     *
     * @param  array  $data  The incoming webhook data containing the status update.
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleStatusUpdate($data)
    {
        $statusData = $data['entry'][0]['changes'][0]['value']['statuses'][0];
        $messageId = $statusData['id'] ?? null;
        $status = $statusData['status'] ?? null;

        if ($messageId && $status) {
            $batchKey = 'chat_status_batch';
            $batchSize = 5000; // Set your batch size (1000, 5000, or 10000)

            // Retrieve the current batch from cache
            $batch = Cache::get($batchKey, []);

            // Add new entry to batch
            $batch[] = [
                'message_id' => $messageId,
                'status' => $status,
                'status_data' => $statusData
            ];

            // If batch reaches the limit, dispatch a single batch job
            if (count($batch) >= $batchSize) {
                BatchUpdateChatStatusJob::dispatch($batch)->onQueue('chat-status-updates');
                Cache::forget($batchKey); // Clear cache after dispatch
            } else {
                // Store batch for future requests
                Cache::put($batchKey, $batch, now()->addMinutes(2));
            }

            // Dispatch the job to the queue
            //UpdateChatStatusJob::dispatch($messageId, $status, $statusData)->onQueue('chat-status-updates');
        }

        return response()->json(['message' => 'Status queued for update'], 200);
    }
}
