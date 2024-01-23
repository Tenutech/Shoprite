<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\State;
use App\Models\ChatTemplate;
use App\Models\ChatCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;

class ChatsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Chats Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/chats')) {
            //Messages
            $messages = ChatTemplate::with([
                'state', 
                'category'
            ])
            ->whereHas('state', function ($query) {
                $query->whereNotIn('name', ['literacy', 'numeracy']);
            })
            ->orderBy('state_id')
            ->orderBy('sort')
            ->get();

            //States
            $states = State::orderBy('sort')->get();

            //Categories
            $categories = ChatCategory::get();

            return view('admin/chats', [
                'messages' => $messages,
                'states' => $states,
                'categories' => $categories
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Chat Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'message' => ['required', 'string'],
            'state' => ['required', 'integer'],
            'category' => ['required', 'integer'],
            'sort' => ['required', 'integer']
        ]);

        try {            
            //Message Create
            $message = ChatTemplate::create([                
                'message' => $request->message,
                'state_id' => $request->state,
                'category_id' => $request->category,
                'sort' => $request->sort
            ]);

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
    | Chat Detail
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
    | Chat Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Message ID
        $messageID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'message' => ['required', 'string'],
            'state' => ['required', 'integer'],
            'category' => ['required', 'integer'],
            'sort' => ['required', 'integer']
        ]);

        try {
            //Message
            $message = ChatTemplate::findorfail($messageID);

            //Messsage Update
            $message->message = $request->message;
            $message->state_id = $request->state;
            $message->category_id = $request->category;
            $message->sort = $request->sort;
            $message->save();

            return response()->json([
                'success' => true,
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
    | Chat Delete
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

    /*
    |--------------------------------------------------------------------------
    | Chat Destroy Multiple
    |--------------------------------------------------------------------------
    */

    public function destroyMultiple(Request $request)
    {
        try {
            $ids = $request->input('ids');
            
            if (is_null($ids) || empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No IDs provided',
                    'error' => 'No IDs provided'
                ], 400);
            }
    
            // Decrypt IDs
            $decryptedIds = array_map(function($id) {
                return Crypt::decryptString($id);
            }, $ids);
    
            DB::beginTransaction();
    
            ChatTemplate::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Messages deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete messages!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
