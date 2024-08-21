<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vacancy;
use App\Models\Application;
use App\Models\Notification;
use App\Jobs\UpdateApplicantData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

class ApplyController extends Controller
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
    | Vacancy Apply
    |--------------------------------------------------------------------------
    */

    public function vacancyApply($id)
    {
        try {
            //User ID
            $userID = Auth::id();

            //Vacancy ID
            $vacancyId = Crypt::decryptString($id);

            //User
            $user = User::findOrFail($userID);

            //Applicant ID
            $applicatId = $user->applicant_id;

            //Vacancy
            $vacancy = Vacancy::findOrFail($vacancyId);

            DB::beginTransaction();

            // Check if the user has already applied for the vacancy
            if (!$user->appliedVacancies->contains($vacancyId)) {
                // Attach the vacancy to the user's applied vacancies
                $application = Application::create([
                    'user_id' => $userID,
                    'vacancy_id' => $vacancyId,
                    'approved' => 'Pending'
                ]);

                if ($application->wasRecentlyCreated) {
                    // Create Notification
                    $notification = new Notification();
                    $notification->user_id = $vacancy->user_id;
                    $notification->causer_id = $userID;
                    $notification->subject()->associate($application); // Assuming you want to associate the notification with the vacancy
                    $notification->type_id = 1;
                    $notification->notification = "Has applied for vacancy 🔔";
                    $notification->read = "No";
                    $notification->save();

                    //Update Applicant Monthly Data
                    UpdateApplicantData::dispatch($applicatId, 'updated', 'Application', $vacancyId)->onQueue('default');
                }
            }

            // Retrieve the application record
            $application = DB::table('applications')->where('user_id', $userID)->where('vacancy_id', $vacancyId)->first();

            DB::commit();

            return response()->json([
                'success' => true,
                'application' => $application,
                'message' => 'Request Sent!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to apply for vacancy!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Application Approve
    |--------------------------------------------------------------------------
    */

    public function approve(Request $request)
    {
        try {
            // User ID
            $userID = Auth::id();

            //Application
            $applicationID = Crypt::decryptString($request->id);
            $application = Application::findOrFail($applicationID);

            //Application Update
            $application->update([
                'approved' => 'Yes'
            ]);

            // If a new connection was updated, then create a notification
            if ($application->wasChanged()) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $application->user_id;
                $notification->causer_id = $userID;
                $notification->subject()->associate($application);
                $notification->type_id = 1;
                $notification->notification = "Approved your application request ✅";
                $notification->read = "No";
                $notification->save();
            }

            $encryptedID = Crypt::encryptString($application->user_id);

            return response()->json([
                'success' => true,
                'encryptedID' => $encryptedID,
                'message' => 'Application approved!',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve application.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Application Decline
    |--------------------------------------------------------------------------
    */

    public function decline(Request $request)
    {
        try {
            // User ID
            $userID = Auth::id();

            //Application
            $applicationID = Crypt::decryptString($request->id);
            $application = Application::findOrFail($applicationID);

            //Application Update
            $application->update([
                'approved' => 'No'
            ]);

            // If a new application was updated, then create a notification
            if ($application->wasChanged()) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $application->user_id;
                $notification->causer_id = $userID;
                $notification->subject()->associate($application);
                $notification->type_id = 1;
                $notification->notification = "Declined your application request 🚫";
                $notification->read = "No";
                $notification->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Application declined!',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to decline application.',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
