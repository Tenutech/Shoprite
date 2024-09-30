<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Role;
use App\Models\Race;
use App\Models\Type;
use App\Models\Town;
use App\Models\Bank;
use App\Models\Chat;
use App\Models\State;
use App\Models\Gender;
use App\Models\Brand;
use App\Models\Reason;
use App\Models\Position;
use App\Models\Duration;
use App\Models\Language;
use App\Models\Applicant;
use App\Models\Education;
use App\Models\Transport;
use App\Models\Disability;
use App\Models\Retrenchment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;

class ApplicantsController extends Controller
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
     * Show the applications dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /*
    |--------------------------------------------------------------------------
    | Applicants Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('manager/applicants')) {
            //User ID
            $userID = Auth::id();

            //Applicants
            $applicants = Applicant::with([
                'town',
                'gender',
                'race',
                'education',
                'duration',
                'brands',
                'role',
                'state',
            ])
            ->where('no_show', '<=', 2)
            ->whereNull('appointed_id')
            ->where(function ($query) use ($userID) {
                $query->whereNull('shortlist_id')
                    ->orWhereHas('shortlist', function ($subQuery) use ($userID) {
                        $subQuery->where('user_id', $userID);
                    });
            })
            ->whereHas('savedBy', function ($query) use ($userID) {
                $query->where('user_id', $userID);
            })
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

            //Towns
            $towns = Town::get();

            //Genders
            $genders = Gender::get();

            //Races
            $races = Race::get();

            //Positions
            $positions = Position::get();

            //Education Levels
            $educations = Education::get();

            //Languages
            $languages = Language::get();

            //Reasons
            $reasons = Reason::get();

            //Durations
            $durations = Duration::get();

            //Retrenchments
            $retrenchments = Retrenchment::get();

            //Brands
            $brands = Brand::get();

            //Transports
            $transports = Transport::get();

            //Disabilities
            $disabilities = Disability::get();

            //Types
            $types = Type::get();

            //Banks
            $banks = Bank::get();

            //Roles
            $roles = Role::get();

            //States
            $states = State::orderBy('sort')->get();

            return view('manager/applicants', [
                'applicants' => $applicants,
                'towns' => $towns,
                'genders' => $genders,
                'races' => $races,
                'positions' => $positions,
                'educations' => $educations,
                'languages' => $languages,
                'reasons' => $reasons,
                'durations' => $durations,
                'retrenchments' => $retrenchments,
                'brands' => $brands,
                'transports' => $transports,
                'disabilities' => $disabilities,
                'types' => $types,
                'banks' => $banks,
                'roles' => $roles,
                'states' => $states
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Applicants
    |--------------------------------------------------------------------------
    */

    public function applicants()
    {
        //Auth User ID
        $userID = Auth::id();

        //Applicants
        $applicants = Applicant::with([
            'town',
            'gender',
            'race',
            'education',
            'duration',
            'brands',
            'role',
            'state',
            'savedBy' => function ($query) use ($userID) {
                $query->where('user_id', $userID);
            }
        ])
        ->where('no_show', '<=', 2)
        ->whereHas('savedBy', function ($query) use ($userID) {
            $query->where('user_id', $userID);
        })
        ->orderBy('firstname')
        ->orderBy('lastname')
        ->get()
        ->map(function ($applicant) {
            $applicant->encrypted_id = Crypt::encryptString($applicant->id);
            return $applicant;
        })
        ->toArray();

        return response()->json([
            'applicants' => $applicants,
        ]);
    }
}
