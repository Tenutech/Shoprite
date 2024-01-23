<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Town;
use App\Models\Gender;
use App\Models\Race;
use App\Models\Vacancy;
use App\Models\Position;
use App\Models\Province;
use App\Models\Applicant;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

class ApplicantApprovalController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except('root');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

     
    /*
    |--------------------------------------------------------------------------
    | Approvals Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/applicant-approvals')) {
            //Auth User ID
            $userID = Auth::id();

            //Applicants
            $applicants = Applicant::with([
                'town',
                'gender',
                'race',
                'position',
                'education',
                'readLanguages',
                'speakLanguages',
                'reason',
                'duration',
                'retrenchment',
                'brand',
                'previousPosition',
                'transport',
                'disability',
                'type',
                'bank',
                'role',
                'state',
                'savedBy' => function ($query) use ($userID) {
                    $query->where('user_id', $userID);
                }
            ])
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get()
            ->map(function ($applicant) {
                $applicant->encrypted_id = Crypt::encryptString($applicant->id);
                return $applicant;
            });

            //Positions
            $positions = Position::whereNotIn('id', [1, 10])->get();

            //Genders
            $genders = Gender::get();

            //Races
            $races = Race::get();

            //Towns
            $towns = Town::get();

            //Provinces
            $provinces = Province::get();        

            return view('admin/applicant-approvals',[
                'applicants' => $applicants,
                'positions' => $positions,
                'genders' => $genders,
                'races' => $races,                
                'towns' => $towns,
                'provinces' => $provinces,
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Vacancy Approve
    |--------------------------------------------------------------------------
    */

    public function approve(Request $request)
    {        
        try {
            $vacancyID = Crypt::decryptString($request->id);

            DB::beginTransaction();

            $vacancy = Vacancy::findOrFail($vacancyID);
            $vacancy->status_id = 2;
            $vacancy->save();
            $vacancy->load('status');

            // Check if vacancy was actually changed
            if ($vacancy->wasChanged()) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $vacancy->user_id;
                $notification->causer_id = Auth::id();
                $notification->subject()->associate($vacancy);
                $notification->type_id = 1;
                $notification->notification = "Has been approved ✅";
                $notification->read = "No";
                $notification->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vacancy Approved Successfully!',
                'vacancy' => $vacancy
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed To Approve Vacancy!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Vacancy Amend
    |--------------------------------------------------------------------------
    */

    public function amend(Request $request)
    {        
        try {
            $vacancyID = Crypt::decryptString($request->id);

            DB::beginTransaction();

            $vacancy = Vacancy::findOrFail($vacancyID);
            $vacancy->status_id = 3;
            $vacancy->save();
            $vacancy->load('status');

            // Check if vacancy was actually changed
            if ($vacancy->wasChanged()) {
                // Create the amendment record
                Amendment::create([
                    'user_id' => $vacancy->user_id,
                    'vacancy_id' => $vacancy->id,
                    'causer_id' => Auth::id(),
                    'description' => $request->input('description'),
                ]);

                // Create Notification
                $notification = new Notification();
                $notification->user_id = $vacancy->user_id;
                $notification->causer_id = Auth::id();
                $notification->subject()->associate($vacancy);
                $notification->type_id = 1;
                $notification->notification = "Needs amendment 📝";
                $notification->read = "No";
                $notification->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vacancy Amended Successfully!',
                'vacancy' => $vacancy
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed To Amend Vacancy!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Vacancy Decline
    |--------------------------------------------------------------------------
    */

    public function decline(Request $request)
    {        
        try {
            $vacancyID = Crypt::decryptString($request->id);

            DB::beginTransaction();

            $vacancy = Vacancy::findOrFail($vacancyID);
            $vacancy->status_id = 4;
            $vacancy->save();
            $vacancy->load('status');

            // Check if vacancy was actually changed
            if ($vacancy->wasChanged()) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $vacancy->user_id;
                $notification->causer_id = Auth::id();
                $notification->subject()->associate($vacancy);
                $notification->type_id = 1;
                $notification->notification = "Has been declined 🚫";
                $notification->read = "No";
                $notification->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vacancy Declined Successfully!',
                'vacancy' => $vacancy
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed To Decline Vacancy!',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}