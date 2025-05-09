<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Applicant;
use App\Models\Company;
use App\Models\Position;
use App\Models\Division;
use App\Models\Race;
use App\Models\Type;
use App\Models\Brand;
use App\Models\Duration;
use App\Models\Education;
use App\Models\NotificationSetting;
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
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailUpdate;

class ProfileSettingsController extends Controller
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
            ];

            $filledFieldsCount = 0;

            foreach ($fields as $field) {
                if (!empty($user->$field)) {
                    $filledFieldsCount++;
                }
            }

            $completionPercentage = ($filledFieldsCount / count($fields)) * 100;

            //User Settings
            $userSettings = NotificationSetting::where('user_id', $userID)->first();

            // Type
            $types = Type::get();

            // Race
            $races = Race::get();

            // Education
            $educations = Education::where('id', '!=', 3)->get();

            // Duration
            $durations = Duration::get();

            // Brand
            $brands = Brand::whereIn('id', [1, 2, 5, 6])->get();

            // Allowed Emails
            $allowedEmails = [
                'sachetty@shoprite.co.za',
                'ljeftha@shoprite.co.za',
                'cjklein@shoprite.co.za',
                'amdeuka@shoprite.co.za',
                'ecoetzee@shoprite.co.za',
                'kbtsepe@shoprite.co.za'
            ];

            // Default to an empty collection
            $divisions = collect();

            // Define allowed users and their corresponding division IDs
            $divisionAccessMap = [
                'sachetty@shoprite.co.za' => [9, 10, 11],
                'ljeftha@shoprite.co.za' => [15, 17, 20],
                'cjklein@shoprite.co.za' => [16, 19, 21],
                'amdeuka@shoprite.co.za' => [7, 8, 12],
                'ecoetzee@shoprite.co.za' => [13, 14],
                'kbtsepe@shoprite.co.za' => [13, 14],
            ];

            // Check if user's email exists in the map
            if (array_key_exists($user->email, $divisionAccessMap)) {
                $allowedDivisionIds = $divisionAccessMap[$user->email];
                $divisions = Division::whereIn('id', $allowedDivisionIds)->get();
            }

            return view('profile-settings', [
                'user' => $user,
                'completionPercentage' => $completionPercentage,
                'userSettings' => $userSettings,
                'types' => $types,
                'races' => $races,
                'educations' => $educations,
                'durations' => $durations,
                'brands' => $brands,
                'allowedEmails' => $allowedEmails,
                'divisions' => $divisions,
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
        // User ID
        $userID = Auth::id();

        // User
        $user = User::findorfail($userID);

        // Allowed Emails
        $allowedEmails = [
            'sachetty@shoprite.co.za',
            'ljeftha@shoprite.co.za',
            'cjklein@shoprite.co.za',
            'amdeuka@shoprite.co.za',
            'ecoetzee@shoprite.co.za',
            'kbtsepe@shoprite.co.za'
        ];

        // Check if only division_id is being changed
        $isAllowedEmail = in_array($user->email, $allowedEmails);
        $otherFieldsChanged = false;

        // Check if any fields other than division_id are being updated
        if ($request->hasFile('avatar')) {
            $otherFieldsChanged = true;
        }
        if ($request->has('firstname') && $request->firstname !== ($user->firstname ?? '')) {
            $otherFieldsChanged = true;
        }
        if ($request->has('lastname') && $request->lastname !== ($user->lastname ?? '')) {
            $otherFieldsChanged = true;
        }
        // Normalize phone number
        if ($request->has('phone') && trim($request->phone) === '+27') {
            $request->merge(['phone' => null]);
        }
        if (!is_null($request->input('phone')) && $request->input('phone') !== ($user->phone ?? '')) {
            $otherFieldsChanged = true;
        }        
        if ($request->has('email') && $request->email !== ($user->email ?? '')) {
            $otherFieldsChanged = true;
        }

        // Check if division_id is being changed
        $divisionIdChanged = $request->has('division_id') && (int)$request->division_id !== $user->division_id;
        $isOnlyDivisionIdChanged = $divisionIdChanged && !$otherFieldsChanged;

        // **Step 1: Check if email was verified in the last 15 minutes**
        if ($user->role_id !== 3 && !($isAllowedEmail && $isOnlyDivisionIdChanged) && !$user->isEmailVerificationValid()) {
            // If verification is outdated, generate a new token and send email
            $user->generateEmailVerificationToken();
            //Mail::to($user->email)->queue(new VerifyEmailUpdate($user));

            return response()->json([
                'success' => false,
                'message' => 'Please verify your email before updating your profile. A verification email has been sent.'
            ], 403);
        }

        // **Step 2: Continue with profile update if email is verified**
        $validationRules = [
            'avatar' => [
                'sometimes',
                'image',
                'mimes:jpg,jpeg,png',
                'mimetypes:image/jpeg,image/png',
                'max:5120',
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                        $fail("The $attribute must be a file of type: jpg, jpeg, png.");
                    }
                }
            ],
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:191', Rule::unique('users')->ignore($userID)],
            'division_id' => ['nullable', Rule::exists('divisions', 'id')],
        ];

        // Conditionally validate `email` based on the `role_id`
        if ($user->role_id < 7) {
            $validationRules['email'] = ['required', 'string', 'email', 'max:191', Rule::unique('users')->ignore($userID)];
        } else {
            $validationRules['email'] = ['nullable', 'string', 'email', 'max:191', Rule::unique('users')->ignore($userID)];
        }

        // Additional validation if `role_id >= 7`
        if ($user->role_id >= 7) {
            $validationRules = array_merge($validationRules, [
                'location' => ['required', 'string', 'max:255'],
                'race_id' => ['required', 'integer', 'exists:races,id'],
                'education_id' => ['required', 'integer', 'exists:educations,id'],
                'duration_id' => ['required', 'integer', 'exists:durations,id'],
                'public_holidays' => ['required', 'in:Yes,No'],
                'environment' => ['required', 'in:Yes,No'],
                'brands' => ['required', 'array', function ($attribute, $value, $fail) {
                    // Check if brand ID 1 is in the array and there are other IDs selected
                    if (in_array(1, $value) && count($value) > 1) {
                        $fail('You cannot select specific brands with "Any".');
                    }
                }],
                'brands.*' => ['required', 'integer', 'exists:brands,id'],
                'disability' => ['required', 'in:Yes,No'],
            ]);
        }

        // Validate request with the dynamically built rules
        $request->validate($validationRules);

        try {
            // Avatar
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');

                // Verify MIME type server-side
                $mimeType = mime_content_type($avatar->getPathname());
                if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid image file type!',
                    ], 400);
                }

                // Validate file signature
                if (!$this->isValidImage($avatar->getPathname())) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid image file!'
                    ], 400);
                }

                // Open and re-encode the image with Intervention
                $image = Image::make($avatar->getPathname())->encode('jpg');

                // Delete old avatar if not default
                if ($user->avatar && $user->avatar !== 'avatar.jpg') {
                    $oldAvatarPath = storage_path('app/public/images/') . $user->avatar;
                    if (File::exists($oldAvatarPath)) {
                        File::delete($oldAvatarPath);
                    }
                }

                // Generate new avatar name and move it
                $avatarName = Str::slug($request->firstname . ' ' . $request->lastname) . '-' . time() . '.' . $avatar->getClientOriginalExtension();
                $avatarPath = storage_path('app/public/images/');
                $avatar->move($avatarPath, $avatarName);
            } else {
                $avatarName = $user->avatar; // Keep the current avatar
            }

            DB::beginTransaction();

            // User Update
            $user->firstname = ucwords($request->firstname);
            $user->lastname = ucwords($request->lastname);
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->address = $request->has('location') ? $request->location : '';
            $user->avatar = $avatarName;

            // Check if division_id is present and user email is in the allowed list
            if ($request->has('division_id') && in_array($user->email, $allowedEmails)) {
                $user->division_id = $request->division_id;
            }

            $user->save();

            // Update Applicant if the role_id is >= 7
            if ($user->role_id >= 7) {
                $applicant = Applicant::find($user->applicant_id);
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $coordinates = $latitude . ',' . $longitude;
                if ($applicant) {
                    $applicant->firstname = ucwords($request->firstname);
                    $applicant->lastname = ucwords($request->lastname);
                    $applicant->has_email = $request->email ? 'Yes' : 'No';
                    $applicant->email = $request->email;
                    $applicant->phone = $request->phone;
                    $applicant->contact_number = $request->phone;
                    $applicant->location = $request->has('location') ? $request->location : '';
                    $applicant->coordinates = $coordinates;
                    $applicant->race_id = $request->race_id;
                    $applicant->education_id = $request->education_id;
                    $applicant->duration_id = $request->duration_id;
                    $applicant->public_holidays = $request->public_holidays;
                    $applicant->environment = $request->environment;
                    $applicant->disability = $request->disability;
                    $applicant->save();

                    // Brands
                    if ($request->has('brands')) {
                        // Get the submitted brands
                        $brands = $request->brands;

                        // If brand with ID 2 is present, add 3 and 4 to the array
                        if (in_array(2, $brands)) {
                            $brands = array_merge($brands, [3, 4]);
                        }

                        // Prepare the data to sync with timestamps
                        $brandData = array_fill_keys($brands, ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);

                        // Sync the brands with the applicant
                        $applicant->brands()->sync($brandData);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'avatar_url' => $user->avatar ? asset('storage/images/' . $user->avatar) : asset('storage/images/avatar.jpg')
            ], 201);
        } catch (ValidationException $e) {
            // Return validation errors with a 422 status code
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile!',
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
        // Retrieve the ID of the currently authenticated user
        $authUserId = Auth::id();

        // Fetch the authenticated user
        $user = User::find($authUserId);

        // **Step 1: Check if email was verified in the last 15 minutes**
        if (!$user->isEmailVerificationValid()) {
            // If verification is outdated, generate a new token and send email
            $user->generateEmailVerificationToken();
            Mail::to($user->email)->queue(new VerifyEmailUpdate($user));

            return response()->json([
                'success' => false,
                'message' => 'Please verify your email before updating your password. A verification email has been sent.'
            ], 403);
        }

        // **Step 2: Custom messages for password validation
        $messages = [
            'newPassword.min' => 'The new password must be at least :min characters.',
            'newPassword.regex' => 'The new password must contain at least one lowercase letter, one uppercase letter, one digit, and one special character.',
            'newPassword.confirmed' => 'The new password confirmation does not match.',
        ];

        // Validate Request
        $request->validate([
            'oldPassword' => ['required', 'string'],
            'newPassword' => [
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
            'message' => 'Password updated successfully!'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Settings Update Notifications
    |--------------------------------------------------------------------------
    */

    public function notificationSettings(Request $request)
    {
        $userId = Auth::id();

        try {
            // Retrieve or create notification settings for the user
            $notificationSettings = NotificationSetting::firstOrCreate(
                ['user_id' => $userId]
            );

            // Prepare data for updating
            $data = $request->all();
            $checkboxFields = [
                'receive_email_notifications',
                'notify_application_submitted',
                'notify_application_status',
                'notify_shortlisted',
                'notify_interview',
                'notify_vacancy_status',
                'notify_new_application',
            ];

            foreach ($checkboxFields as $field) {
                // Check if the field is present in the request
                if (isset($data[$field])) {
                    // Convert 'on' to true, absence to false
                    $data[$field] = $data[$field] === 'on';
                } else {
                    // If the checkbox was not sent in the request, set to false
                    $data[$field] = false;
                }
            }

            // Update the notification settings with the prepared data
            $notificationSettings->fill($data);
            $notificationSettings->save();

            // Return a success response
            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Image
    |--------------------------------------------------------------------------
    */

    // Function to validate the file signature (magic bytes)
    private function isValidImage($path)
    {
        $file = fopen($path, 'rb');
        $bytes = fread($file, 8); // Get the first few bytes
        fclose($file);

        // Check only the first four bytes for JPEG and PNG
        $firstFourBytes = substr(bin2hex($bytes), 0, 8);

        // JPEG and PNG magic numbers
        if (in_array($firstFourBytes, ['ffd8ffe0', 'ffd8ffe1', '89504e47'])) {
            return true; // It's a valid JPEG or PNG file
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Verify Email Before Update
    |--------------------------------------------------------------------------
    */

    /**
     * Handle email verification for profile updates.
     *
     * This method verifies the email verification token provided in the request.
     * If the token is valid and has not expired, the user is allowed to proceed
     * with profile updates. Otherwise, an error response is returned.
     *
     * @param Request $request The incoming request containing the verification token.
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function verifyEmailUpdate(Request $request)
    {
        $user = User::where('email_verification_token', $request->token)->first();

        if (!$user || $user->email_verification_expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification link.'
            ], 400);
        }

        // **Update email_verified_at to now() instead of clearing token**
        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->email_verification_expires_at = null;
        $user->save();

        return redirect()->route('profile-settings.index')->with('success', 'Email verified. You may now update your profile.');
    }
}
