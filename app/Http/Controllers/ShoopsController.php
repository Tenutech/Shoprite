<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Chat;
use Illuminate\Http\Request;
use App\Services\ChatService;
use Illuminate\Support\Facades\Log;

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
        try {
            // Extract message ID and status from the webhook data
            $statusData = $data['entry'][0]['changes'][0]['value']['statuses'][0];
            $messageId = $statusData['id'] ?? null;
            $status = $statusData['status'] ?? null;

            // Ensure we have a valid message ID and status
            if (!$messageId || !$status) {
                return response()->json(['error' => 'Missing message ID or status'], 400);
            }

            // Find the chat record by message ID
            $chat = Chat::where('message_id', $messageId)->first();

            if ($chat) {
                // Handle a failed message status
                if ($status === 'failed') {
                    // Extract error details
                    $errorCode = $statusData['errors'][0]['code'] ?? null;
                    $errorReason = $statusData['errors'][0]['title'] ?? 'Unknown reason';

                    // Update the chat record with the failed status, code, and reason
                    $chat->update([
                        'status' => 'Failed',
                        'code' => $errorCode,
                        'reason' => $errorReason,
                    ]);
                } else {
                    // Update the chat record with the new status (e.g., 'delivered', 'sent')
                    $chat->update([
                        'status' => ucfirst($status),
                    ]);
                }

                return response()->json(['message' => 'Status updated successfully'], 200);
            } else {
                return response()->json(['error' => 'Chat record not found'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
