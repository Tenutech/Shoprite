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
use Illuminate\Http\Request;
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
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ManagersController extends Controller
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
        if (view()->exists('admin.managers')) {
            // Auth User
            $authUser = Auth::user();

            //Users
            $users = User::with([
                'role',
                'status',
                'gender',
                'store',
                'files',
                'division',
                'region',
                'brand'
            ])
            ->where('role_id', 6)
            ->orderby('firstname')
            ->orderby('lastname')
            ->get();

            //Genders
            $genders = Gender::all();

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

            return view('admin/managers', [
                'authUser' => $authUser,
                'users' => $users,
                'genders' => $genders,
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

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'avatar' => ['image' ,'mimes:jpg,jpeg,png','max:1024'],
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:191', 'unique:users'],
            'id_number' => ['sometimes', 'nullable', 'string',  'digits:13', 'unique:users'],
            'id_verified' => ['sometimes', 'nullable', 'string', 'in:Yes,No'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'age' => ['sometimes', 'nullable', 'integer', 'min:16', 'max:100'],
            'gender_id' => ['sometimes', 'nullable', 'integer', 'exists:genders,id'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'store_id' => ['sometimes', 'nullable', 'integer', 'exists:stores,id'],
            'division_id' => ['sometimes', 'nullable', 'integer', 'exists:divisions,id'],
            'region_id' => ['sometimes', 'nullable', 'integer', 'exists:regions,id'],
            'brand_id' => ['sometimes', 'nullable', 'integer', 'exists:brands,id']
        ]);

        try {
            // Avatar
            if ($request->avatar) {
                $avatar = request()->file('avatar');
                $avatarName = $request->firstname . ' ' . $request->lastname . '-' . time() . '.' . $avatar->getClientOriginalExtension();
                $avatarPath = public_path('/images/');
                $avatar->move($avatarPath, $avatarName);
            } else {
                $avatarName = 'avatar.jpg';
            }

            DB::beginTransaction();

            //User Create
            $user = User::create([
                'firstname' => ucwords($request->firstname),
                'lastname' => ucwords($request->lastname),
                'email' => $request->email,
                'email_verified_at' => now(),
                'phone' => $request->phone,
                'id_number' => $request->id_number,
                'id_verified' => $request->id_verified,
                'password' => Hash::make("Shoprite1!"),
                'avatar' => $avatarName,
                'birth_date' => date('Y-m-d', strtotime($request->birth_date)),
                'age' => $request->age,
                'gender_id' => $request->gender_id,
                'role_id' => $request->role_id,
                'store_id' => $request->store_id,
                'division_id' => $request->division_id,
                'region_id' => $request->region_id,
                'brand_id' => $request->brand_id,
                'status_id' => 2,
            ]);

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
                'gender',
                'store',
                'region',
                'division',
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
            'phone' => ['sometimes', 'nullable', 'string', 'max:191', Rule::unique('users')->ignore($userID)],
            'id_number' => ['sometimes', 'nullable', 'string',  'digits:13', Rule::unique('users')->ignore($userID)],
            'id_verified' => ['sometimes', 'nullable', 'string', 'in:Yes,No'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'age' => ['sometimes', 'nullable', 'integer', 'min:16', 'max:100'],
            'gender_id' => ['sometimes', 'nullable', 'integer', 'exists:genders,id'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'store_id' => ['sometimes', 'nullable', 'integer', 'exists:stores,id'],
            'division_id' => ['sometimes', 'nullable', 'integer', 'exists:divisions,id'],
            'region_id' => ['sometimes', 'nullable', 'integer', 'exists:regions,id'],
            'brand_id' => ['sometimes', 'nullable', 'integer', 'exists:brands,id']
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
                $avatarName = $request->firstname . ' ' . $request->lastname . '-' . time() . '.' . $avatar->getClientOriginalExtension();
                $avatarPath = public_path('/images/');
                $avatar->move($avatarPath, $avatarName);
            } else {
                $avatarName = $user->avatar;
            }

            DB::beginTransaction();

            //User Update
            $user->firstname = ucwords($request->firstname);
            $user->lastname = ucwords($request->lastname);
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->id_number = $request->id_number;
            $user->id_verified = $request->id_verified;
            $user->avatar = $avatarName;
            $user->birth_date = date('Y-m-d', strtotime($request->birth_date));
            $user->age = $request->age;
            $user->gender_id = $request->gender_id;
            $user->role_id = $request->role_id;
            $user->store_id = $request->store_id;
            $user->division_id = $request->division_id;
            $user->region_id = $request->region_id;
            $user->brand_id = $request->brand_id;
            $user->save();

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

    /*
    |--------------------------------------------------------------------------
    | Export Users
    |--------------------------------------------------------------------------
    */

    public function export(Request $request)
    {
        try {
            // Retrieve the path to the Python interpreter from Laravel's config/services.php
            $pythonPath = config('services.python.path');

            // Define the path to the Python script that will handle the export logic
            $scriptPath = base_path('python/exports/users_export.py');

            // Create a new Symfony Process to execute the Python script
            $process = new Process([$pythonPath, $scriptPath]);

            // Set a timeout of 300 seconds (5 minutes) to prevent long-running scripts from hanging
            $process->setTimeout(300);

            // Execute the process
            $process->run();

            // Check if the process was successful; if not, throw an exception
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Get the output from the Python script, which should be the full file path to the generated CSV
            $output = trim($process->getOutput());

            // Verify that the file exists at the specified path
            if (!file_exists($output)) {
                // Return a 500 error if the file was not created/found
                return response()->json(['message' => 'Export file not found.'], 500);
            }

            // Return the file as a downloadable response and delete it after sending
            return response()->download($output, basename($output))
                            ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            // Catch any exceptions and return a 400 error with the error message
            return response()->json([
                'message' => 'An error occurred during export.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
