<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use App\Models\Position;
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

class UsersController extends Controller
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
    | Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin.users')) {
            //Users
            $users = User::with([
                'role',
                'status',
                'company',
                'position',
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
                'notifications'
            ])
            ->where('role_id', '>', 1)
            ->orderby('firstname')
            ->orderby('lastname')
            ->get();

            //Roles
            $roles = Role::where('id', '>', 1)
                         ->orderby('name')
                         ->get();

            return view('admin/users',[
                'users' => $users,
                'roles' => $roles
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | User Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'avatar' => ['image' ,'mimes:jpg,jpeg,png','max:1024'],
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users'],
            'phone' => ['required', 'string', 'max:191', 'unique:users'],
            'company' => ['required', 'string', 'max:191'],
            'position' => ['required', 'string', 'max:191'],
        ]);

        try {            
            // Avatar
            if ($request->avatar) {               
                $avatar = request()->file('avatar');
                $avatarName = $request->firstname.' '.$request->lastname.'-'.time().'.'.$avatar->getClientOriginalExtension();
                $avatarPath = public_path('/images/');
                $avatar->move($avatarPath, $avatarName);
            } else {
                $avatarName = 'avatar.jpg';
            }

            DB::beginTransaction();

            // Check if the company exists or create a new one
            $inputCompanyName = strtolower($request->company);
            $company = Company::whereRaw('LOWER(name) = ?', [$inputCompanyName])->first();

            if (!$company) {
                $formattedCompanyName = ucwords($request->company);
                
                $company = Company::create([
                    'name' => $formattedCompanyName,
                    'icon' => 'ri-building-line',
                    'color' => 'secondary'
                ]);
            }

            // Check if the position exists or create a new one
            $inputPositionName = strtolower($request->position);
            $position = Position::whereRaw('LOWER(name) = ?', [$inputPositionName])->first();

            if (!$position) {
                $formattedPositionName = ucwords($request->position);
                $position = Position::create([
                    'name' => $formattedPositionName,
                    'icon' => 'ri-user-fill',
                    'color' => 'info'
                ]);
            }
            
            //User Create
            $user = User::create([                
                'firstname' => ucwords($request->firstname),
                'lastname' => ucwords($request->lastname),
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make("F4!pT9@gL2#dR0wZ"),
                'avatar' => $avatarName,
                'company_id' => $company->id,
                'position_id' => $position->id,
                'role_id' => $request->role,
                'status_id' => 2,
            ]);

            DB::commit();

            $encID = Crypt::encryptString($user->id);

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

            $user = User::with(['company', 'position'])->findOrFail($userID);

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
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users,email,' . $userID],
            'phone' => ['required', 'string', 'max:191', 'unique:users,phone,' . $userID],
            'company' => ['required', 'string', 'max:191'],
            'position' => ['required', 'string', 'max:191'],
        ]);

        try {
            //User
            $user = User::findorfail($userID);

            // Avatar
            if ($request->avatar) {
                // Check if a previous avatar exists and is not the default one
                if ($user->avatar && $user->avatar !== 'avatar.jpg') {
                    // Construct the path to the old avatar
                    $oldAvatarPath = public_path('/images/') . $user->avatar;
                    // Check if the file exists and delete it
                    if (File::exists($oldAvatarPath)) {
                        File::delete($oldAvatarPath);
                    }
                }
                
                $avatar = request()->file('avatar');
                $avatarName = $request->firstname.' '.$request->lastname.'-'.time().'.'.$avatar->getClientOriginalExtension();
                $avatarPath = public_path('/images/');
                $avatar->move($avatarPath, $avatarName);
            } else {
                $avatarName = $user->avatar;
            }

            DB::beginTransaction();

            // Check if the company exists or create a new one
            $inputCompanyName = strtolower($request->company);
            $company = Company::whereRaw('LOWER(name) = ?', [$inputCompanyName])->first();

            if (!$company) {
                $formattedCompanyName = ucwords($request->company);
                
                $company = Company::create([
                    'name' => $formattedCompanyName,
                    'icon' => 'ri-building-line',
                    'color' => 'secondary'
                ]);
            }

            // Check if the position exists or create a new one
            $inputPositionName = strtolower($request->position);
            $position = Position::whereRaw('LOWER(name) = ?', [$inputPositionName])->first();

            if (!$position) {
                $formattedPositionName = ucwords($request->position);
                $position = Position::create([
                    'name' => $formattedPositionName,
                    'icon' => 'ri-user-fill',
                    'color' => 'info'
                ]);
            }

            //User Update
            $user->firstname = ucwords($request->firstname);
            $user->lastname = ucwords($request->lastname);
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->avatar = $avatarName;
            $user->company_id = $company->id;
            $user->position_id = $position->id;
            $user->role_id = $request->role;
            $user->save();

            DB::commit();

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
            $decryptedIds = array_map(function($id) {
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