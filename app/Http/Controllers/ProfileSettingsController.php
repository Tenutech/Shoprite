<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Spatie\Activitylog\Models\Activity;

class ProfileSettingsController extends Controller
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
    | Profile Settings Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        if (view()->exists('profile-settings')) {
            //User ID
            $userID = Auth::id();

            //User
            $user = User::with([
                'role', 
                'status', 
                'company', 
                'position'
            ])
            ->findorfail($userID);

            //Completed
            $fields = [
                'firstname',
                'lastname',
                'email',
                'phone',
                'avatar',
                'company_id',
                'position_id',
                'website'
            ];
            
            $filledFieldsCount = 0;
            
            foreach ($fields as $field) {
                if (!empty($user->$field)) {
                    $filledFieldsCount++;
                }
            }
            
            $completionPercentage = ($filledFieldsCount / count($fields)) * 100;

            return view('profile-settings',[
                'user' => $user,
                'completionPercentage' => $completionPercentage
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Settings Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //User ID
        $userID = Auth::id();

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
            $user = User::find($userID);

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
            $user->website = $request->website;
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile Updated Successfully!'
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed To Update Profile!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Settings Update Password
    |--------------------------------------------------------------------------
    */

    public function updatePassword(Request $request)
    {
        //User
        $user = auth()->user();
        
        // Validate Request
        $request->validate([
            'oldPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Check if the old password matches
        if (!Hash::check($request->oldPassword, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The old password is incorrect.'
            ], 400); // Bad Request
        }

        // Update user password
        $user->password = Hash::make($request->newPassword);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password Updated Successfully!'
        ]);
    }
}