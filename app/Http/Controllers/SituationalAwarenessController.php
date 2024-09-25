<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\State;
use App\Models\ChatTemplate;
use App\Models\ChatCategory;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\AssessmentRequest;

class SituationalAwarenessController extends Controller
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
    | Situational Awareness Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/situational-awareness')) {
            //Messages
            $messages = ChatTemplate::with([
                'state',
                'category'
            ])
            ->whereHas('state', function ($query) {
                $query->whereIn('name', ['Situational Awareness']);
            })
            ->orderBy('state_id')
            ->orderBy('sort')
            ->get();

            return view('admin/situational-awareness', [
                'messages' => $messages
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Situational Awareness Add
    |--------------------------------------------------------------------------
    */

    public function store(AssessmentRequest $request)
    {
        //Validate
        $request->validated();

        try {
            //State ID
            $stateID = State::where('code', 'situational_awareness')->value('id');

            //Category ID
            $categoryID = ChatCategory::where('name', 'situational_awareness')->value('id');

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
    | Situational Awareness Details
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
    | Situational Awareness Update
    |--------------------------------------------------------------------------
    */

    public function update(AssessmentRequest $request)
    {
        //Message ID
        $messageID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validated();

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
    | Situational Awareness Destroy
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
