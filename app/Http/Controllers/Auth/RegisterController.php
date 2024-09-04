<?php

namespace App\Http\Controllers\Auth;

use App\Models\NotificationSetting;
use App\Jobs\ProcessUserIdNumber;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\Applicant;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Apply the 'guest' middleware to all methods
        // This means only guests (unauthenticated users) can access the registration methods
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // Define validation rules for registration data
        return Validator::make($data, [
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'id_number' => ['required', 'string', 'digits:13', 'unique:users'],
            'phone' => ['required', 'string', 'max:191', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'avatar' => ['image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'address' => ['required', 'string', 'max:255']
        ]);
    }

    /**
     * Create a new user instancze after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // Handle avatar upload if provided
        if (request()->has('avatar')) {
            $avatar = request()->file('avatar');
            $avatarName = $data['firstname'] . ' ' . $data['lastname'] . '-' . time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
        } else {
            $avatarName = 'avatar.jpg'; // Default avatar
        }

        // Check if an applicant exists with the given ID number
        $applicant = Applicant::where('id_number', $data['id_number'])->first();

        // Create a new user record
        $user = User::create([
            'firstname' => ucwords($data['firstname']),
            'lastname' => ucwords($data['lastname']),
            'email' => $data['email'],
            'phone' => $data['phone'],
            'id_number' => $data['id_number'],
            'password' => Hash::make($data['password']),
            'avatar' => $avatarName,
            'role_id' => 4, // Default role for new users
            'applicant_id' => $applicant ? $applicant->id : null,
            'status_id' => 1, // User status (e.g., active)
            'address' => $data['address'],
        ]);

        // Create default notification settings for the user
        NotificationSetting::create([
            'user_id' => $user->id,
        ]);

        // Calculate the user's age from the ID number
        $age = $this->calculateAgeFromId($data['id_number']);

        // If the user is under 18, create a consent record
        if ($age < 18) {
            // Consent::create([
            //     'user_id' => $user->id,
            //     'guardian_mobile' => $data['guardian_mobile'],
            //     'consent_status' => 'Pending',
            // ]);
        }

        // Dispatch the job to process the user's ID number
        ProcessUserIdNumber::dispatch($user->id);

        return $user;
    }

    /**
     * The user has been registered.
     * This method is called after a user has successfully registered.
     * You can use it to perform additional tasks upon registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user  The registered user instance
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        // Redirect the user based on their role_id
        switch ($user->role_id) {
            case 1:
            case 2:
                return redirect('/admin/home'); // Redirect admins to admin home
            case 3:
                return redirect('/manager/home'); // Redirect managers to manager home
            default:
                return redirect('/home'); // Fallback for any other cases
        }
    }

    private function calculateAgeFromId(string $idNumber)
    {
        $year = substr($idNumber, 0, 2);
        $month = substr($idNumber, 2, 2);
        $day = substr($idNumber, 4, 2);

        $currentYear = date('Y');
        $year = ($year > date('y')) ? '19' . $year : '20' . $year;

        $birthDate = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        $today = new \DateTime();

        return $today->diff($birthDate)->y;
    }
}
