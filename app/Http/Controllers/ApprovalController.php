<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Town;
use App\Models\Type;
use App\Models\Brand;
use App\Models\Vacancy;
use App\Models\Position;
use App\Models\Province;
use App\Models\Amendment;
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

class ApprovalController extends Controller
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
        if (view()->exists('admin/approvals')) {
            //User ID
            $userID = Auth::id();

            //User
            $user = User::with('vacancies')->findOrFail($userID);

            //Vacancies
            $vacancies = Vacancy::with([
                'user',
                'position.tags',
                'store.brand',
                'store.town',
                'type',
                'status',
                'applicants',
                'savedBy'
            ])
            ->get();

            //Positions
            $positions = Position::whereNotIn('id', [1, 10])->get();

            //Types
            $types = Type::get();

            //Brands
            $brands = Brand::get();

            //Towns
            $towns = Town::get();

            //Provinces
            $provinces = Province::get();

            //Card Data
            $totalVacancies = Vacancy::count();
            $pendingVacancies = Vacancy::where('status_id', 1)->count();
            $approvedVacancies = Vacancy::where('status_id', 2)->count();
            $amendVacancies = Vacancy::where('status_id', 3)->count();

            //Previous Month
            $prevMonth = now()->subMonth();

            //Previous Month Data
            $previousMonthVacancies = Vacancy::whereMonth('created_at', $prevMonth->month)->whereYear('created_at', $prevMonth->year)->count();
            $previousMonthPending = Vacancy::where('status_id', 1)->whereMonth('created_at', $prevMonth->month)->whereYear('created_at', $prevMonth->year)->count();
            $previousMonthApproved = Vacancy::where('status_id', 2)->whereMonth('created_at', $prevMonth->month)->whereYear('created_at', $prevMonth->year)->count();
            $previousMonthAmend = Vacancy::where('status_id', 3)->whereMonth('created_at', $prevMonth->month)->whereYear('created_at', $prevMonth->year)->count();

            //Current Month Data
            $currentMonthVacancies = Vacancy::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
            $currentMonthPending = Vacancy::where('status_id', 1)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
            $currentMonthApproved = Vacancy::where('status_id', 2)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
            $currentMonthAmend = Vacancy::where('status_id', 3)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

            //Movement
            $movementTotal = $totalVacancies != 0 ? round((($currentMonthVacancies - $previousMonthVacancies) / $totalVacancies) * 100, 2) : 0;
            $movementPending = $pendingVacancies != 0 ? round((($currentMonthPending - $previousMonthPending) / $pendingVacancies) * 100, 2) : 0;
            $movementApproved = $approvedVacancies != 0 ? round((($currentMonthApproved - $previousMonthApproved) / $approvedVacancies) * 100, 2) : 0;
            $movementAmend = $amendVacancies != 0 ? round((($currentMonthAmend - $previousMonthAmend) / $amendVacancies) * 100, 2) : 0;            

            return view('admin/approvals',[
                'vacancies' => $vacancies,
                'positions' => $positions,
                'types' => $types,
                'brands' => $brands,                
                'towns' => $towns,
                'provinces' => $provinces,
                'totalVacancies' => $totalVacancies,
                'pendingVacancies' => $pendingVacancies,
                'approvedVacancies' => $approvedVacancies,
                'amendVacancies' => $amendVacancies,
                'movementTotal' => $movementTotal,
                'movementPending' => $movementPending,
                'movementApproved' => $movementApproved,
                'movementAmend' => $movementAmend
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
                $notification->notification = "Has been approved 🎉";
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
                $notification->notification = "Has been declined ❌";
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