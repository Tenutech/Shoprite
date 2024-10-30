<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Role;
use App\Models\Race;
use App\Models\Type;
use App\Models\Town;
use App\Models\Bank;
use App\Models\Store;
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

            //User
            $user = User::findOrFail($userID);

            //Store
            $store = Store::find($user->store_id);

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

        //User
        $user = User::findOrFail($userID);

        //Store
        $store = Store::find($user->store_id);

        // Check if the store has valid coordinates
        $storeCoordinates = isset($store->coordinates) && strpos($store->coordinates, ',') !== false ? explode(',', $store->coordinates) : null;

        $longitude = '';
        $latitude = '';

        if ($storeCoordinates) {
            list($latitude, $longitude) = [(float)$storeCoordinates[0], (float)$storeCoordinates[1]];
        }

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
        ->whereNull('appointed_id')
        ->where('no_show', '<=', 2)
        ->whereHas('savedBy', function ($query) use ($userID) {
            $query->where('user_id', $userID);
        })
        ->orderBy('firstname')
        ->orderBy('lastname')
        ->get()
        ->map(function ($applicant) use ($latitude, $longitude, $storeCoordinates) {
            // Encrypt applicant ID
            $applicant->encrypted_id = Crypt::encryptString($applicant->id);

            // Now, calculate the distance for each applicant if store coordinates exist
            if ($storeCoordinates && isset($applicant->coordinates) && strpos($applicant->coordinates, ',') !== false) {
                list($applicantLat, $applicantLng) = explode(',', $applicant->coordinates);

                // Convert strings to floats
                $applicantLat = (float)$applicantLat;
                $applicantLng = (float)$applicantLng;

                // Check if both coordinates are numeric
                if (is_numeric($latitude) && is_numeric($longitude) && is_numeric($applicantLat) && is_numeric($applicantLng)) {
                    // Haversine formula to calculate the distance
                    $earthRadius = 6371;  // Earth's radius in kilometers
                    $dLat = deg2rad($latitude - $applicantLat);
                    $dLng = deg2rad($longitude - $applicantLng);

                    $a = sin($dLat / 2) * sin($dLat / 2) +
                        cos(deg2rad($latitude)) * cos(deg2rad($applicantLat)) *
                        sin($dLng / 2) * sin($dLng / 2);

                    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                    $distance = $earthRadius * $c;  // Distance in kilometers

                    // Round the distance to 1 decimal place
                    $applicant->distance = round($distance, 1);
                } else {
                    $applicant->distance = 'N/A';  // Coordinates are not numeric
                }
            } else {
                $applicant->distance = 'N/A';  // No store coordinates or applicant coordinates
            }

            return $applicant;
        })
        ->toArray();

        return response()->json([
            'applicants' => $applicants,
        ]);
    }
}
