<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Setting;
use App\Models\ReminderSetting;
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

class SettingsController extends Controller
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
    | Weighting Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/settings')) {
            //Settings
            $settings = Setting::all();

            //Reminders
            $reminders = ReminderSetting::all();

            return view('admin/settings', [
                'settings' => $settings,
                'reminders' => $reminders
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    public function update(Request $request) {    
        try {
            // List of all settings keys you expect to update
            $settingsKeys = [
                'vacancy_posting_duration',
                'shortlist_expiry',
                'session_timeout',
                'min_shortlist_number',
                'max_shortlist_number'
            ];

            foreach ($settingsKeys as $key) {
                // Fetch each setting by key
                $setting = Setting::where('key', $key)->first();

                // If the setting exists, update its value
                if ($setting) {
                    $setting->update([
                        'value' => $request->input($key, '1') // Default to '1' if not provided
                    ]);
                }
            }

            // Return a success response
            return response()->json([
                'success' => true,
                'message' => 'Settings Updated Successfully.'
            ]);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed To Update Settings!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reminder Settings
    |--------------------------------------------------------------------------
    */

    public function reminderSettings(Request $request) {    
        try {
            // Define an array of all possible reminder types
            $reminderTypes = [
                'vacancy_created_no_shortlist',
                'shortlist_created_no_interview',
                'interview_scheduled_no_vacancy_filled'
            ];

            foreach ($reminderTypes as $type) {
                // Check if the reminder type was present in the request
                // This assumes unchecked checkboxes will not be sent in the request
                $isActive = $request->has($type);
    
                // Update the reminder's active status based on whether it was checked or not
                ReminderSetting::where('type', $type)->update(['is_active' => $isActive ? 1 : 0]);
            }    
    
            // Return a success response
            return response()->json([
                'success' => true,
                'message' => 'Reminders Updated Successfully.'
            ]);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed To Update Reminders!',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
