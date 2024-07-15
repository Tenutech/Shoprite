<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\State;
use App\Models\ChatTemplate;
use App\Models\ChatCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class NumeracyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /*
    |--------------------------------------------------------------------------
    | Numeracy Index
    |--------------------------------------------------------------------------
    */
    
    public function index()
    {
        if (view()->exists('admin/numeracy')) {
            //Messages
            $messages = ChatTemplate::with([
                'state', 
                'category'
            ])
            ->whereHas('state', function ($query) {
                $query->whereIn('name', ['numeracy']);
            })
            ->orderBy('state_id')
            ->orderBy('sort')
            ->get();

            return view('admin/numeracy', [
                'messages' => $messages
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Numeracy Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'message' => ['required', 'string'],
            'answer' => ['required', 'in:a,b,c,d'],
            'sort' => ['required', 'integer']
        ]);

        try {
            //State ID
            $stateID = State::where('code', 'numeracy')->value('id');

            //Category ID
            $categoryID = ChatCategory::where('name', 'numeracy')->value('id');

            // Message Create
            $message = ChatTemplate::create([                
                'message' => $request->message,
                'state_id' => $stateID ? $stateID : null,
                'category_id' => $categoryID ? $categoryID : null,
                'answer' => $request->answer,
                'sort' => $request->sort
            ]);

            $message->load('state', 'category');

            $encID = Crypt::encryptString($message->id);

            return response()->json([
                'success' => true,
                'chat' => $message,
                'encID' => $encID,
                'message' => 'Message created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create message!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Numeracy Details
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $messageID = Crypt::decryptString($id);

            $message = ChatTemplate::with([
                'state', 
                'category'
            ])->findOrFail($messageID);

            return response()->json([
                'chat' => $message,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Numeracy Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Message ID
        $messageID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'message' => ['required', 'string'],
            'answer' => ['required', 'in:a,b,c,d'],
            'sort' => ['required', 'integer']
        ]);

        try {
            //Message
            $message = ChatTemplate::findorfail($messageID);

            //Messsage Update
            $message->message = $request->message;
            $message->answer = $request->answer;
            $message->sort = $request->sort;
            $message->save();

            return response()->json([
                'success' => true,
                'chat' => $message,
                'encID' => $request->field_id,                
                'message' => 'Message updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update message!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Numeracy Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $messageID = Crypt::decryptString($id);

            $message = ChatTemplate::findOrFail($messageID);
            $message->delete();

            return response()->json([
                'success' => true,
                'chat' => $message,
                'message' => 'Message deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete message!',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
