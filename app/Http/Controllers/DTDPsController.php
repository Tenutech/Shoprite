<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Role;
use App\Models\Gender;
use App\Models\Company;
use App\Models\Position;
use App\Models\Store;
use App\Models\Division;
use App\Models\Region;
use App\Models\Brand;
use App\Jobs\ProcessUserIdNumber;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;

class DTDPsController extends Controller
{
    private UserService $userService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserService $userService)
    {
        $this->middleware(['auth', 'verified']);
        $this->userService = $userService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin.dtdps')) {
            //Users
            $users = User::with([
                'role',
                'status',
                'company',
                'position',
                'gender',
                'store',
                'applicant',
                'amendments',
                'state',
                'vacancies',
                'appliedVacancies',
                'savedVacancies',
                'savedApplicants',
                'files',
                'messagesFrom',
                'messagesTo',
                'notifications',
                'division',
                'region',
                'brand'
            ])
            ->where('role_id', 4)
            ->orderby('firstname')
            ->orderby('lastname')
            ->get();

            //Genders
            $genders = Gender::all();

            //Companies
            $companies = Company::all();

            //Positions
            $positions = Position::all();

            //Stores
            $stores = Store::with([
                'brand',
                'town'
            ])->get();

            //Roles
            $roles = Role::where('id', '>', 1)
                         ->orderby('name')
                         ->get();

            //Divisions
            $divisions = Division::all();

            //Regions
            $regions = Region::all();

            //Brands
            $brands = Brand::all();

            return view('admin/dtdps', [
                'users' => $users,
                'genders' => $genders,
                'companies' => $companies,
                'positions' => $positions,
                'stores' => $stores,
                'roles' => $roles,
                'divisions' => $divisions,
                'regions' => $regions,
                'brands' => $brands
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | User Add
    |--------------------------------------------------------------------------
    */

    public function store(StoreUserRequest $request)
    {
        //Validate
        $request = $request->validated();

        try {
            DB::beginTransaction();

            //User Create
            $user = $this->userService->store($request);

            DB::commit();

            $encID = Crypt::encryptString($user->id);

            // Dispatch the job
            ProcessUserIdNumber::dispatch($user->id);

            return response()->json([
                'success' => true,
                'user' => $user,
                'encID' => $encID,
                'message' => 'User created successfully!',
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | User Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $userID = Crypt::decryptString($id);

            $user = User::with([
                'role',
                'status',
                'company',
                'position',
                'gender',
                'store',
                'division',
                'region',
                'brand',
            ])->findOrFail($userID);

            return response()->json([
                'user' => $user,
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
    | User Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //User ID
        $userID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'avatar' => ['image' ,'mimes:jpg,jpeg,png','max:1024'],
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'email' => ['required', 'string', 'email', 'max:191', Rule::unique('users')->ignore($userID)],
            'phone' => ['required', 'string', 'max:191', Rule::unique('users')->ignore($userID)],
            'id_number' => ['required', 'string',  'digits:13', Rule::unique('users')->ignore($userID)],
            'id_verified' => ['sometimes', 'nullable', 'string', 'in:Yes,No'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'age' => ['sometimes', 'nullable', 'integer', 'min:16', 'max:100'],
            'gender_id' => ['sometimes', 'nullable', 'integer', 'exists:genders,id'],
            'resident' => ['sometimes', 'nullable', 'integer', 'in:0,1'],
            'position_id' => ['sometimes', 'nullable', 'integer', 'exists:positions,id'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'store_id' => ['sometimes', 'nullable', 'integer', 'exists:stores,id'],
            'region_id' => ['sometimes', 'nullable', 'integer', 'exists:regions,id'],
            'division_id' => ['sometimes', 'nullable', 'integer', 'exists:divisions,id'],
            'brand_id' => ['sometimes', 'nullable', 'integer', 'exists:brands,id'],
            'internal' => ['sometimes', 'nullable', 'integer', 'in:0,1']
        ]);

        try {
            //User
            $user = User::findorfail($userID);

            // Avatar
            $avatarName = $user->avatar;
            if (isset($request->avatar)) {
                $avatarName = $this->userService->checkAvatar($request, $user->avatar);
            }

            DB::beginTransaction();

            // Update User
            $user = $this->userService->update($request, $user, $avatarName);

            DB::commit();

            // Dispatch the job
            ProcessUserIdNumber::dispatch($user->id);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!'
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | User Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $userID = Crypt::decryptString($id);

            $delete = User::findOrFail($userID);
            $delete->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | User Delete Multiple
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

            User::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Users deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete users!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
