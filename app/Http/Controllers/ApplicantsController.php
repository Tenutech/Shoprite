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
use App\Models\Company;
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
use App\Jobs\ProcessUserIdNumber;
use App\Services\ApplicantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\StoreApplicantRequest;
use App\Http\Requests\UpdateApplicantRequest;

class ApplicantsController extends Controller
{
    private ApplicantService $applicantService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ApplicantService $applicantService)
    {
        $this->middleware(['auth', 'verified']);
        $this->applicantService = $applicantService;
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

    /*
    |--------------------------------------------------------------------------
    | Admins Applicants
    |--------------------------------------------------------------------------
    */

    public function adminsApplicants()
    {
        if (view()->exists('admin.applicants')) {
            //Applicants
            $applicants = Applicant::with([
                'role',
                'position',
                'gender',
                'user',
                'state',
                'type',
                'savedBy'
            ])
            ->orderby('firstname')
            ->orderby('lastname')
            ->get();

            //Genders
            $genders = Gender::all();

            //Positions
            $positions = Position::all();

            //Roles
            $roles = Role::where('id', '>', 1)
                         ->orderby('name')
                         ->get();

            //States
            $states = State::orderBy('sort')->get();

            //Types
            $types = Type::get();

            return view('admin/applicants', [
                'applicants' => $applicants,
                'genders' => $genders,
                'positions' => $positions,
                'states' => $states,
                'roles' => $roles,
                'types' => $types
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant Add
    |--------------------------------------------------------------------------
    */

    public function store(StoreApplicantRequest $request)
    {
        //Validate
        $request = $request->validated();

        try {
            DB::beginTransaction();

            //Applicant Create
            $applicant = $this->applicantService->store($request);

            DB::commit();

            $encID = Crypt::encryptString($applicant->id);

            // Dispatch the job
            ProcessUserIdNumber::dispatch(null, $applicant->id);

            return response()->json([
                'success' => true,
                'user' => $applicant,
                'encID' => $encID,
                'message' => 'Applicant created successfully!',
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create applicant!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $applicantID = Crypt::decryptString($id);

            $applicant = Applicant::with([
                'role',
                'position',
                'gender',
                'user',
                'state',
                'type'
            ])->findOrFail($applicantID);

            return response()->json([
                'applicant' => $applicant,
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
    | Applicant Update
    |--------------------------------------------------------------------------
    */

    public function update(UpdateApplicantRequest $request)
    {
        //Applicant ID
        $applicantID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validated();

        try {
            //Applicant
            $applicant = Applicant::findorfail($applicantID);

            //Avatar
            $avatarName = $applicant->avatar;
            if (isset($request->avatar)) {
                $avatarName = $this->applicantService->checkAvatar($request, $avatarName);
            }

            DB::beginTransaction();

            //Applicant Update
            $applicant = $this->applicantService->update($request, $applicant, $avatarName);

            DB::commit();

            //Dispatch the job
            ProcessUserIdNumber::dispatch(null, $applicant->id);

            return response()->json([
                'success' => true,
                'message' => 'Applicant updated successfully!'
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update applicant!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $applicantID = Crypt::decryptString($id);

            $delete = Applicant::findOrFail($applicantID);
            $delete->delete();

            return response()->json([
                'success' => true,
                'message' => 'Applicant deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete applicant!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant Delete Multiple
    |--------------------------------------------------------------------------
    */

    public function destroyMultiple(Request $request)
    {
        try {
            $ids = $request->input('ids');

            if (is_null($ids) || empty($ids)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No IDs provided'
                ], 400);
            }

            // Decrypt IDs
            $decryptedIds = array_map(function ($id) {
                return Crypt::decryptString($id);
            }, $ids);

            DB::beginTransaction();

            Applicant::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Applicants deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete applicants!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
