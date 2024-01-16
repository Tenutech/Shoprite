<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Chat;
use App\Models\State;
use App\Models\Applicant;
use App\Services\ChatService;
use App\Jobs\SendWhatsAppMessage;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

class MessageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->middleware('auth')->except('root');
        $this->chatService = $chatService;
    }    

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /*
    |--------------------------------------------------------------------------
    | Message Create
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {        
        try {
            $applicantID = $request->applicant_id;

            // Retrieve the state_id for the state with code 'free'
            $stateID = State::where('code', 'free')->first()->id;

            // Update the applicant's state_id
            Applicant::where('id', $applicantID)->update(['state_id' => $stateID]);

            // Send a WhatsApp message to the applicant
            $applicant = Applicant::find($applicantID);
            SendWhatsAppMessage::dispatch($applicant, $request->message);

            // Retrieve the last chat message for the applicant
            $chat = Chat::where('applicant_id', $applicantID)->orderBy('created_at', 'desc')->first();

            return response()->json([
                'success' => true,
                'chat' => $chat,
                'message' => 'Message Sent!'
            ], 201);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed To Send Message!',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}