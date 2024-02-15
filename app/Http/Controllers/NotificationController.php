<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Connection;
use App\Models\Opportunity;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

class NotificationController extends Controller
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
    | Notification Read
    |--------------------------------------------------------------------------
    */

    public function notificationRead(Request $request)
    {
        try {
            //Notifications
            $encryptedNotificationIds = $request->input('notifications');
            $notificationIds = array_map(function ($id) {
                return Crypt::decryptString($id);
            }, $encryptedNotificationIds);

            $notifications = Notification::whereIn('id', $notificationIds)->get();

            DB::beginTransaction();

            foreach ($notifications as $notification) {
                $notification->read = 'Yes';
                $notification->save();
    
                if ($notification->subject_type === Message::class) {
                    $message = Message::find($notification->subject_id);
                    if ($message) {
                        $message->read = 'Yes';
                        $message->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Marked as Read!'
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to merk as read!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Notification Remove
    |--------------------------------------------------------------------------
    */

    public function notificationRemove(Request $request)
    {
        try {
            //Notifications
            $encryptedNotificationIds = $request->input('notifications');
            $notificationIds = array_map(function ($id) {
                return Crypt::decryptString($id);
            }, $encryptedNotificationIds);

            $notifications = Notification::whereIn('id', $notificationIds)->get();

            DB::beginTransaction();

            foreach ($notifications as $notification) {
                $notification->read = 'Yes';
                $notification->show = 'No';
                $notification->save();
    
                if ($notification->subject_type === Message::class) {
                    $message = Message::find($notification->subject_id);
                    if ($message) {
                        $message->read = 'Yes';
                        $message->save();
                    }
                }
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Notifications removed!',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove notifications.',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}