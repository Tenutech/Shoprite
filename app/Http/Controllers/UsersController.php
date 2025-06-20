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
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
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
            // Auth User
            $authUser = Auth::user();

            // Users
            $users = User::with([
                'role',
                'status',
                'gender',
                'store',
                'applicant',
                'state',
                'files'
            ])
            ->where('role_id', 7)
            ->orderby('firstname')
            ->orderby('lastname')
            ->take(10)
            ->get();

            // Genders
            $genders = Gender::all();

            // Stores
            $stores = Store::with([
                'brand',
                'town'
            ])->get();

            // Roles
            $roles = Role::where('id', '>', 1)
                         ->orderby('name')
                         ->get();

            // Divisions
            $divisions = Division::all();

            // Regions
            $regions = Region::all();

            return view('admin/users', [
                'authUser' => $authUser,
                'users' => $users,
                'genders' => $genders,
                'stores' => $stores,
                'roles' => $roles,
                'divisions' => $divisions,
                'regions' => $regions
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
            'id_number' => ['required', 'string',  'digits:13', 'unique:users'],
            'id_verified' => ['sometimes', 'nullable', 'string', 'in:Yes,No'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'age' => ['sometimes', 'nullable', 'integer', 'min:16', 'max:100'],
            'gender_id' => ['sometimes', 'nullable', 'integer', 'exists:genders,id'],
            'role_id' => ['required', 'integer', 'exists:roles,id']
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
                'phone' => $request->phone,
                'id_number' => $request->id_number,
                'id_verified' => $request->id_verified,
                'password' => Hash::make("Shoprite1!"),
                'avatar' => $avatarName,
                'birth_date' => date('Y-m-d', strtotime($request->birth_date)),
                'age' => $request->age,
                'gender_id' => $request->gender_id,
                'role_id' => $request->role_id,
                'status_id' => 2
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
            'role_id' => ['required', 'integer', 'exists:roles,id']
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

            // Check if the company exists or create a new one
            $inputCompanyName = strtolower($request->company);
            $company = Company::whereRaw('LOWER(name) = ?', [$inputCompanyName])->first();

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
    | Password Reset
    |--------------------------------------------------------------------------
    */

    public function passwordReset(Request $request)
    {
        try {
            //User ID
            $userID = Crypt::decryptString($request->password_id);

            // Custom messages for password validation
            $messages = [
                'password.min' => 'The password must be at least :min characters.',
                'password.regex' => 'The password must contain at least one lowercase letter, one uppercase letter, one digit, and one special character.',
                'password.confirmed' => 'The password confirmation does not match.'
            ];


            //Validate
            $request->validate([
                'password_id' => ['required', 'string', function ($attribute, $value, $fail) use ($userID) {
                    // Check if the user exists
                    if (!User::where('id', $userID)->exists()) {
                        $fail('The user does not exist.');
                    }
                }],
                'password' => [
                    'required',
                    'string',
                    'min:8', // Increase the minimum length to 12 characters
                    'regex:/[a-z]/', // At least one lowercase letter
                    'regex:/[A-Z]/', // At least one uppercase letter
                    'regex:/[0-9]/', // At least one digit
                    'regex:/[@$!%*#?&]/', // At least one special character
                    'confirmed'
                ],
            ], $messages);

            //User Update
            $user = User::findOrFail($userID);
            $user->password = Hash::make($request->password); // Use password, not role_id
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully!',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation failure
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Return a failure response with the error message
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | User Pagination
    |--------------------------------------------------------------------------
    */

    public function fetchUsers(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10); // Default to 10 items per page
            $search = $request->get('search', ''); // Search keyword

            // Start building the query; add any relationships if needed (e.g., 'profile')
            $usersQuery = User::with(['gender', 'status'])
                ->where('role_id', 7)
                ->orderBy('firstname')
                ->orderBy('lastname');

            // Apply search filters if a search term is provided
            if (!empty($search)) {
                $lowerSearch = strtolower($search);
                $searchTerms = array_filter(explode(' ', $lowerSearch)); // Split search into words and remove empties

                $usersQuery->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->where(function ($q) use ($term) {
                            $q->whereRaw('LOWER(firstname) LIKE ?', ["%$term%"])
                            ->orWhereRaw('LOWER(lastname) LIKE ?', ["%$term%"])
                            ->orWhereRaw('LOWER(email) LIKE ?', ["%$term%"])
                            ->orWhereRaw('LOWER(phone) LIKE ?', ["%$term%"])
                            ->orWhereRaw('LOWER(id_number) LIKE ?', ["%$term%"])
                            ->orWhereHas('gender', function ($q) use ($term) {
                                $q->whereRaw('LOWER(name) LIKE ?', ["%$term%"]);
                            });
                        });
                    }
                });
            }

            // Paginate results
            $users = $usersQuery->paginate($perPage);

            // Attach an encrypted ID to each user
            $users->getCollection()->transform(function ($user) {
                $user->encrypted_id = Crypt::encryptString($user->id);
                return $user;
            });

            return response()->json([
                'success'       => true,
                'current_page'  => $users->currentPage(),
                'last_page'     => $users->lastPage(),
                'prev_page_url' => $users->previousPageUrl(),
                'next_page_url' => $users->nextPageUrl(),
                'data'          => $users->items(),
                'path'          => $users->path(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users!',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export filtered users data to an Excel report.
     *
     * This method retrieves users data based on search input
     * and exports it as an Excel file
     *
     * @param Request $request The incoming HTTP request containing filters.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse An Excel file download response.
     */
    public function export(Request $request)
    {
        try {
            $authUserId = Auth::id();
            $authUser = User::find($authUserId);

            $search = $request->input('search');

            // Get credentials using config (not env)
            $dbConnection = config('database.default'); // usually 'mysql'
            $dbConfig = config("database.connections.$dbConnection");

            $pythonPath = config('services.python.path');
            $scriptPath = base_path('python/exports/users_export.py');
            $process = new Process([
                $pythonPath,
                $scriptPath,
                '--auth_user', json_encode($authUser),
                '--search', $search,
            ]);

            // Set credentials as environment variables
            $test = $process->setEnv([
                'DB_HOST' => $dbConfig['host'],
                'DB_PORT' => (string) $dbConfig['port'],
                'DB_DATABASE' => $dbConfig['database'],
                'DB_USERNAME' => $dbConfig['username'],
                'DB_PASSWORD' => $dbConfig['password'],
            ]);

            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = trim($process->getOutput());

            if (!file_exists($output)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Export file not found.',
                ], 500);
            }

            return response()->download($output, basename($output))
                ->deleteFileAfterSend(true);
        } catch (Exception $e) {
            // Handle any errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during export.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
