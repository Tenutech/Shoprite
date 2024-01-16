<?php

namespace App\Http\Controllers;

use Exception;
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
        // Use the ChatService to process the incoming message.
        $this->chatService->handleIncomingMessage($request->all());

        // Respond to the request indicating the message has been received and processed.
        return response()->json(['message' => 'Received'], 200);
    }
}