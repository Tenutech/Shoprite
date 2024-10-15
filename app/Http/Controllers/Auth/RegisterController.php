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
use Illuminate\Support\Facades\Log;

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
            'id_number' => ['required', 'string', 'digits:13', 'unique:users', function ($attribute, $value, $fail) {
                // Check if the ID number is valid
                if (!$this->isValidSAIdNumber($value)) {
                    $fail('You have not entered a valid SA ID Number.');
                }

                // Check if the user is under 18
                if ($this->calculateAgeFromId($value) < 18) {
                    $fail('You are under 18 and not eligible to register on the platform.');
                }
            }],
            'phone' => ['required', 'string', 'max:191', 'unique:users'],
            'email' => ['nullable', 'string', 'email', 'max:191', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed']
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
        // Check if an applicant exists with the given ID number
        $applicant = Applicant::where('id_number', $data['id_number'])->first();

        // Create a new user record
        $user = User::create([
            'firstname' => ucwords($data['firstname']),
            'lastname' => ucwords($data['lastname']),
            'email' => $data['email'],
            'email_verified_at' => now(),
            'phone' => $data['phone'],
            'id_number' => $data['id_number'],
            'password' => Hash::make($data['password']),
            'avatar' => 'avatar.jpg',
            'company_id' => 1,
            'role_id' => config('constants.new_user_role_id'), // Default role for new users
            'applicant_id' => $applicant ? $applicant->id : null,
            'status_id' => 1, // User status (e.g., active)
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
        ProcessUserIdNumber::dispatch($user->id, null);

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
                return redirect('/rpp/home'); // Redirect rpp to rpp home
            case 4:
                return redirect('/dtdp/home'); // Redirect rpp and dtdp to dtdp home
            case 5:
                return redirect('/dpp/home'); // Redirect dpp to dpp home
            case 6:
                return redirect('/manager/home'); // Redirect managers to manager home
            default:
                return redirect('/home'); // Fallback for any other cases
        }
    }

    /**
     * Function to validate a South African ID number.
     *
     * @param string $id
     * @return bool
     */
    public static function isValidSAIdNumber(string $id): bool
    {
        $id = preg_replace('/\D/', '', $id); // Ensure the ID is only digits

        if (strlen($id) != 13) {
            return false; // Early return if ID length is incorrect
        }

        $sum = 0;
        $length = strlen($id);
        for ($i = 0; $i < $length - 1; $i++) { // Exclude the last digit for the main loop
            $number = (int)$id[$i];
            if (($length - $i) % 2 === 0) {
                $number = $number * 2;
                if ($number > 9) {
                    $number = $number - 9;
                }
            }
            $sum += $number;
        }

        // Calculate checksum based on the sum
        $checksum = (10 - ($sum % 10)) % 10;

        // Last digit of the ID should match the calculated checksum
        return (int)$id[$length - 1] === $checksum;
    }

    /**
     * Function to calculate the user's age based on their ID number.
     *
     * @param string $idNumber
     * @return int
     */
    private function calculateAgeFromId(string $idNumber): int
    {
        // Extract the first two digits for the year of birth (YY)
        $year = (int) substr($idNumber, 0, 2);

        // Determine if the century is 19xx or 20xx based on the current short year (last two digits of the current year)
        $currentYearShort = (int) date('y'); // Last two digits of the current year
        $year = ($year > $currentYearShort) ? (1900 + $year) : (2000 + $year);

        // Extract the month of birth (MM)
        $month = (int) substr($idNumber, 2, 2);

        // Extract the day of birth (DD)
        $day = (int) substr($idNumber, 4, 2);

        // Ensure valid day and month values
        if (!checkdate($month, $day, $year)) {
            throw new \Exception('Invalid birth date extracted from ID number.');
        }

        // Create a DateTime object from the extracted birth date (YYYY-MM-DD format)
        $birthDate = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day");

        // Check if the birthDate is valid
        if (!$birthDate || $birthDate->format('Y-m-d') !== "$year-$month-$day") {
            throw new \Exception('Invalid birth date extracted from ID number.');
        }

        // Create a DateTime object for the current date
        $today = new \DateTime();

        // Calculate the difference between today and the birth date to get the age in years
        return $today->diff($birthDate)->y;
    }
}
