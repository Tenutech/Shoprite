<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Type;
use App\Models\User;
use App\Models\Race;
use App\Models\Bank;
use App\Models\State;
use App\Models\Brand;
use App\Models\Store;
use App\Models\Gender;
use App\Models\Reason;
use App\Models\Document;
use App\Models\Language;
use App\Models\Position;
use App\Models\Duration;
use App\Models\Education;
use App\Models\Applicant;
use App\Models\Transport;
use App\Models\Disability;
use App\Models\Retrenchment;
use App\Models\Notification;
use App\Models\ChatTemplate;
use App\Models\ScoreWeighting;
use App\Jobs\ProcessUserIdNumber;
use App\Jobs\SendIdNumberToSap;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Services\GoogleMapsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

class ApplicationController extends Controller
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
    | Application Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('application')) {
            //User ID
            $userID = Auth::id();

            //User
            $user = User::with([
                'applicant.brands',
                'appliedVacancies'
            ])
            ->withCount('appliedVacancies')
            ->findOrFail($userID);

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

            //Literacy
            $literacyQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['literacy']);
            })
            ->inRandomOrder()
            ->get();

            //Numeracy
            $numeracyQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['numeracy']);
            })
            ->inRandomOrder()
            ->get();

            //Situational
            $situationalQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['situational']);
            })
            ->inRandomOrder()
            ->get();

            return view('application', [
                'userID' => $userID,
                'user' => $user,
                'types' => $types,
                'races' => $races,
                'educations' => $educations,
                'durations' => $durations,
                'brands' => $brands,
                'literacyQuestions' => $literacyQuestions,
                'numeracyQuestions' => $numeracyQuestions,
                'situationalQuestions' => $situationalQuestions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Application Create
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        // Validate Input
        $request->validate([
            'consent' => ['accepted'], // Validate consent checkbox
            'avatar' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'], // Avatar validation
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'id_number' => ['required', 'string', 'max:13', 'unique:applicants'],
            'phone' => ['required', 'string', 'max:191', 'unique:applicants'],
            'location' => ['required', 'string'],
            'latitude' => ['required', function ($attribute, $value, $fail) {
                if (empty($value)) {
                    $fail('Please select a verified address from the Google suggestions.');
                }
            }],
            'longitude' => ['required', function ($attribute, $value, $fail) {
                if (empty($value)) {
                    $fail('Please select a verified address from the Google suggestions.');
                }
            }],
            'race_id' => ['required', 'integer', 'exists:races,id'],
            'email' => ['sometimes', 'nullable', 'string', 'email', 'max:191', 'unique:applicants'],
            'education_id' => ['required', 'integer', 'exists:educations,id'],
            'duration_id' => ['required', 'integer', 'exists:durations,id'],
            'public_holidays' => ['required', 'in:Yes,No'],
            'environment' => ['required', 'in:Yes,No'],
            'brands' => ['required', 'array', function ($attribute, $value, $fail) {
                // Check if brand ID 1 is in the array and there are other IDs selected
                if (in_array(1, $value) && count($value) > 1) {
                    $fail('You cannot select specific brands with "Any".');
                }
            }], // Ensure brands is an array
            'brands.*' => ['required', 'integer', 'exists:brands,id'], // Validate each brand id exists in the brands table
            'disability' => ['required', 'in:Yes,No'],
            'literacy_answers' => ['required', 'array'], // Ensure literacy answers array
            'literacy_answers.*' => ['required', 'in:a,b,c,d,e'], // Validate each literacy answer
            'numeracy_answers' => ['required', 'array'],
            'numeracy_answers.*' => ['required', 'in:a,b,c,d,e'], // Validate each numeracy answer
            'situational_answers' => ['required', 'array'],
            'situational_answers.*' => ['required', 'in:a,b,c,d,e'], // Validate each situational answer
        ]);

        try {
            // Get the current authenticated user
            $userId = Auth::id();
            $user = User::find($userId);

            // Handle avatar upload (if present)
            if ($request->avatar) {
                $avatar = request()->file('avatar');
                $avatarName = '/images/' . $request->firstname . ' ' . $request->lastname . '-' . time() . '.' . $avatar->getClientOriginalExtension();
                $avatarPath = public_path('/images/');
                $avatar->move($avatarPath, $avatarName);

                // Create a document record for the uploaded avatar
                Document::create([
                    'applicant_id' => null, // This will be set later when the applicant is created
                    'name' => $avatarName,
                    'type' => $avatar->getClientOriginalExtension(),
                    'size' => $avatar->getSize(),
                    'url' => '/images/' . $avatarName,
                ]);
            } else {
                // Use existing avatar if available, otherwise fallback to default
                $avatarName = $user && $user->avatar ? '/images/' . $user->avatar : '/images/avatar.jpg';
            }

            // Determine the value of avatar_upload based on the avatar
            $avatarUpload = $avatarName == '/images/avatar.jpg' ? 'No' : 'Yes'; // If avatar is the default one, set 'No', otherwise 'Yes'

            // Get form data from the request
            $firstname = $request->firstname;
            $lastname = $request->lastname;
            $idNumber = $request->id_number;
            $phone = $request->phone;
            $location = $request->location;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $coordinates = $latitude . ',' . $longitude;
            $raceID = $request->race_id;
            $email = $request->email;
            $educationId = $request->education_id;
            $durationId = $request->duration_id;
            $publicHolidays = $request->public_holidays;
            $environment = $request->environment;
            $brands = $request->brands;
            $disability = $request->disability;
            $literacyAnswers = $request->literacy_answers;
            $numeracyAnswers = $request->numeracy_answers;
            $situationalAnswers = $request->situational_answers;

            // Fetch questions for literacy, numeracy, and situational assessments
            $literacyQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['literacy']);
            })->get();

            $numeracyQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['numeracy']);
            })->get();

            $situationalQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['situational']);
            })->get();

            // Calculate scores for literacy, numeracy, and situational assessments
            $literacyScore = 0;
            $literacyQuestionsCount = $literacyQuestions->count();
            foreach ($literacyQuestions as $question) {
                if (isset($literacyAnswers[$question->id]) && $literacyAnswers[$question->id] == $question->answer) {
                    $literacyScore++;
                }
            }

            $numeracyScore = 0;
            $numeracyQuestionsCount = $numeracyQuestions->count();
            foreach ($numeracyQuestions as $question) {
                if (isset($numeracyAnswers[$question->id]) && $numeracyAnswers[$question->id] == $question->answer) {
                    $numeracyScore++;
                }
            }

            $situationalScore = 0;
            $situationalQuestionsCount = $situationalQuestions->count();
            foreach ($situationalQuestions as $question) {
                if (isset($situationalAnswers[$question->id]) && $situationalAnswers[$question->id] == $question->answer) {
                    $situationalScore++;
                }
            }

            // Get the 'complete' state ID
            $completeStateID = State::where('code', 'complete')->value('id');

            DB::beginTransaction(); // Begin transaction for DB operations

            // Create the applicant record with form data
            $applicant = Applicant::create([
                'phone' => $phone,
                'id_number' => $idNumber,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'race_id' => $raceID,
                'avatar_upload' => $avatarUpload, // Save avatar upload status (Yes or No)
                'avatar' => '/images/avatar.jpg',
                'terms_conditions' => $request->consent ? 'Yes' : 'No', // Check if the user accepted terms
                'additional_contact_number' => 'No',
                'contact_number' => $phone,
                'public_holidays' => $publicHolidays, // Store user's answer to public holidays
                'education_id' => $educationId,
                'consent' => $request->consent ? 'Yes' : 'No', // Store consent status
                'environment' => $environment, // Store user's answer to environment
                'duration_id' => $durationId,
                'location_type' => 'Address',
                'location' => $location,
                'coordinates' => $coordinates,
                'has_email' => $email ? 'Yes' : 'No', // Determine if email was provided
                'email' => $email,
                'disability' => $disability,
                'literacy_score' => $literacyScore, // Save literacy score
                'literacy_questions' => $literacyQuestionsCount, // Total literacy questions
                'literacy' => "{$literacyScore}/{$literacyQuestionsCount}", // Format score
                'numeracy_score' => $numeracyScore, // Save numeracy score
                'numeracy_questions' => $numeracyQuestionsCount, // Total numeracy questions
                'numeracy' => "{$numeracyScore}/{$numeracyQuestionsCount}", // Format score
                'situational_score' => $situationalScore, // Save situational score
                'situational_questions' => $situationalQuestionsCount, // Total situational questions
                'situational' => "{$situationalScore}/{$situationalQuestionsCount}", // Format score
                'role_id' => 8,
                'applicant_type_id' => 2,
                'application_type' => 'Website', // Application type set to Website
                'state_id' => $completeStateID,
            ]);

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

            // Verify the applicant's location using Google Maps API
            $googleMapsService = new GoogleMapsService();
            $geocodedAddress = $googleMapsService->geocodeAddress($location);

            if ($geocodedAddress) {
                // Update applicant location with geocoded data
                $applicant->update([
                    'location' => $geocodedAddress['formatted_address'],
                    'town_id' => $geocodedAddress['city'],
                    'coordinates' => $geocodedAddress['latitude'] . ' ' . $geocodedAddress['longitude']
                ]);
            }

            // Calculate the applicant's overall score
            $score = $this->calculateScore($applicant);
            $applicant->score = $score;
            $applicant->save(); // Save updated applicant

            // Update the user's applicant ID (if applicant is created successfully)
            if ($applicant) {
                $user->applicant_id = $applicant->id;
                $user->save(); // Update user with applicant ID
            }

            // Dispatch a background job for ID number processing
            ProcessUserIdNumber::dispatch(null, $applicant->id);

            // Dispatch the new job to process the ID number, pass the applicant object as well
            SendIdNumberToSap::dispatch($applicant->id_number, $applicant);

            // Send notification if the applicant was created successfully
            if ($applicant->wasRecentlyCreated) {
                $notification = new Notification();
                $notification->user_id = $userId;
                $notification->causer_id = Auth::id();
                $notification->subject()->associate($applicant); // Associate applicant with the notification
                $notification->type_id = 1;
                $notification->notification = "Submitted application 🔔";
                $notification->read = "No";
                $notification->save(); // Save notification
            }

            DB::commit(); // Commit the transaction

            // Encrypt the applicant's ID before sending it in the response
            $encryptedID = Crypt::encryptString($applicant->id);

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully!',
                'applicant' => $applicant,
                'encrypted_id' => $encryptedID
            ], 201); // Return success response with applicant data
        } catch (Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of error

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit application!',
                'error' => $e->getMessage()
            ], 400); // Return error response
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Application Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Applicant ID
        $applicantId = Crypt::decryptString($request->id);

        //Applicant
        $applicant = Applicant::findOrFail($applicantId);
        
        //Validate Input
        $request->validate([
            'consent' => ['accepted'], // Validate consent checkbox
            'avatar' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'], // Avatar validation
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'id_number' => ['required', 'string', 'max:13', Rule::unique('applicants')->ignore($applicantId)],
            'phone' => ['required', 'string', 'max:191', Rule::unique('applicants')->ignore($applicantId)],
            'location' => ['required', 'string'],
            'latitude' => ['required', function ($attribute, $value, $fail) {
                if (empty($value)) {
                    $fail('Please select a verified address from the Google suggestions.');
                }
            }],
            'longitude' => ['required', function ($attribute, $value, $fail) {
                if (empty($value)) {
                    $fail('Please select a verified address from the Google suggestions.');
                }
            }],
            'race_id' => ['required', 'integer', 'exists:races,id'],
            'email' => ['sometimes', 'nullable', 'string', 'email', 'max:191', Rule::unique('applicants')->ignore($applicantId)],
            'education_id' => ['required', 'integer', 'exists:educations,id'],
            'duration_id' => ['required', 'integer', 'exists:durations,id'],
            'public_holidays' => ['required', 'in:Yes,No'],
            'environment' => ['required', 'in:Yes,No'],
            'brands' => ['required', 'array', function ($attribute, $value, $fail) {
                // Check if brand ID 1 is in the array and there are other IDs selected
                if (in_array(1, $value) && count($value) > 1) {
                    $fail('You cannot select specific brands with "Any".');
                }
            }], // Ensure brands is an array
            'brands.*' => ['required', 'integer', 'exists:brands,id'], // Validate each brand id exists in the brands table
            'disability' => ['required', 'in:Yes,No'],
            'literacy_answers' => ['required', 'array'], // Ensure literacy answers array
            'literacy_answers.*' => ['required', 'in:a,b,c,d,e'], // Validate each literacy answer
            'numeracy_answers' => ['required', 'array'],
            'numeracy_answers.*' => ['required', 'in:a,b,c,d,e'], // Validate each numeracy answer
            'situational_answers' => ['required', 'array'],
            'situational_answers.*' => ['required', 'in:a,b,c,d,e'], // Validate each situational answer
        ]);

        try {
            // Get the current authenticated user
            $userId = Auth::id();
            $user = User::find($userId);

            // Handle avatar upload (if present)
            if ($request->avatar) {
                $avatar = request()->file('avatar');
                $avatarName = '/images/' . $request->firstname . ' ' . $request->lastname . '-' . time() . '.' . $avatar->getClientOriginalExtension();
                $avatarPath = public_path('/images/');
                $avatar->move($avatarPath, $avatarName);

                // Create a document record for the uploaded avatar
                Document::create([
                    'applicant_id' => null, // This will be set later when the applicant is created
                    'name' => $avatarName,
                    'type' => $avatar->getClientOriginalExtension(),
                    'size' => $avatar->getSize(),
                    'url' => '/images/' . $avatarName,
                ]);
            } else {
                // Use existing avatar if available, otherwise fallback to default
                $avatarName = $user && $user->avatar ? $user->avatar : '/images/avatar.jpg';
            }

            // Determine the value of avatar_upload based on the avatar
            $avatarUpload = $avatarName == '/images/avatar.jpg' ? 'No' : 'Yes'; // If avatar is the default one, set 'No', otherwise 'Yes'

            // Get form data from the request
            $firstname = $request->firstname;
            $lastname = $request->lastname;
            $idNumber = $request->id_number;
            $phone = $request->phone;
            $location = $request->location;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $coordinates = $latitude . ',' . $longitude;
            $raceID = $request->race_id;
            $email = $request->email;
            $educationId = $request->education_id;
            $durationId = $request->duration_id;
            $publicHolidays = $request->public_holidays;
            $environment = $request->environment;
            $disability = $request->disability;
            $literacyAnswers = $request->literacy_answers;
            $numeracyAnswers = $request->numeracy_answers;
            $situationalAnswers = $request->situational_answers;

            // Fetch questions for literacy, numeracy, and situational assessments
            $literacyQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['literacy']);
            })->get();

            $numeracyQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['numeracy']);
            })->get();

            $situationalQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['situational']);
            })->get();

            // Calculate scores for literacy, numeracy, and situational assessments
            $literacyScore = 0;
            $literacyQuestionsCount = $literacyQuestions->count();
            foreach ($literacyQuestions as $question) {
                if (isset($literacyAnswers[$question->id]) && $literacyAnswers[$question->id] == $question->answer) {
                    $literacyScore++;
                }
            }

            $numeracyScore = 0;
            $numeracyQuestionsCount = $numeracyQuestions->count();
            foreach ($numeracyQuestions as $question) {
                if (isset($numeracyAnswers[$question->id]) && $numeracyAnswers[$question->id] == $question->answer) {
                    $numeracyScore++;
                }
            }

            $situationalScore = 0;
            $situationalQuestionsCount = $situationalQuestions->count();
            foreach ($situationalQuestions as $question) {
                if (isset($situationalAnswers[$question->id]) && $situationalAnswers[$question->id] == $question->answer) {
                    $situationalScore++;
                }
            }

            // Get the 'complete' state ID
            $completeStateID = State::where('code', 'complete')->value('id');

            DB::beginTransaction();

            // Applicant Create
            $applicant->update([
                'phone' => $phone,
                'id_number' => $idNumber,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'race_id' => $raceID,
                'avatar_upload' => $avatarUpload, // Save avatar upload status (Yes or No)
                'avatar' => '/images/avatar.jpg',
                'terms_conditions' => $request->consent ? 'Yes' : 'No', // Check if the user accepted terms
                'additional_contact_number' => 'No',
                'contact_number' => $phone,
                'public_holidays' => $publicHolidays, // Store user's answer to public holidays
                'education_id' => $educationId,
                'consent' => $request->consent ? 'Yes' : 'No', // Store consent status
                'environment' => $environment, // Store user's answer to environment
                'duration_id' => $durationId,
                'location_type' => 'Address',
                'location' => $location,
                'coordinates' => $coordinates,
                'has_email' => $email ? 'Yes' : 'No', // Determine if email was provided
                'email' => $email,
                'disability' => $disability,
                'literacy_score' => $literacyScore, // Save literacy score
                'literacy_questions' => $literacyQuestionsCount, // Total literacy questions
                'literacy' => "{$literacyScore}/{$literacyQuestionsCount}", // Format score
                'numeracy_score' => $numeracyScore, // Save numeracy score
                'numeracy_questions' => $numeracyQuestionsCount, // Total numeracy questions
                'numeracy' => "{$numeracyScore}/{$numeracyQuestionsCount}", // Format score
                'situational_score' => $situationalScore, // Save situational score
                'situational_questions' => $situationalQuestionsCount, // Total situational questions
                'situational' => "{$situationalScore}/{$situationalQuestionsCount}", // Format score
                'role_id' => 8,
                'applicant_type_id' => 2,
                'application_type' => 'Website', // Application type set to Website
                'state_id' => $completeStateID,
            ]);

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

            // Now let's verify the location using GoogleMapsService
            $googleMapsService = new GoogleMapsService();
            $geocodedAddress = $googleMapsService->geocodeAddress($location);

            if ($geocodedAddress) {
                // Update the applicant's location with the formatted address and town_id
                $applicant->update([
                    'location' => $geocodedAddress['formatted_address'],
                    'town_id' => $geocodedAddress['city'],
                    'coordinates' => $geocodedAddress['latitude'] . ' ' . $geocodedAddress['longitude']
                ]);
            }

            // Verify the applicant's location using Google Maps API
            $googleMapsService = new GoogleMapsService();
            $geocodedAddress = $googleMapsService->geocodeAddress($location);

            if ($geocodedAddress) {
                // Update applicant location with geocoded data
                $applicant->update([
                    'location' => $geocodedAddress['formatted_address'],
                    'town_id' => $geocodedAddress['city'],
                    'coordinates' => $geocodedAddress['latitude'] . ' ' . $geocodedAddress['longitude']
                ]);
            }

            // Calculate the applicant's overall score
            $score = $this->calculateScore($applicant);
            $applicant->score = $score;
            $applicant->save(); // Save updated applicant

            // Update the user's applicant ID (if applicant is created successfully)
            if ($applicant) {
                $user->applicant_id = $applicant->id;
                $user->save(); // Update user with applicant ID
            }

            // If a new applicant was created, then create a notification
            if ($applicant->wasRecentlyCreated) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $userId;
                $notification->causer_id = Auth::id();
                $notification->subject()->associate($applicant);
                $notification->type_id = 1;
                $notification->notification = "Updated your application 🔔";
                $notification->read = "No";
                $notification->save();
            }

            DB::commit(); // Commit the transaction

            // Encrypt the applicant's ID before sending it in the response
            $encryptedID = Crypt::encryptString($applicant->id);

            return response()->json([
                'success' => true,
                'message' => 'Application updated successfully!',
                'applicant' => $applicant,
                'encrypted_id' => $encryptedID
            ], 201); // Return success response with applicant data
        } catch (Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of error

            return response()->json([
                'success' => false,
                'message' => 'Failed to update application!',
                'error' => $e->getMessage()
            ], 400); // Return error response
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Application Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $applicantId = Crypt::decryptString($id);

            //Delete Application
            Applicant::destroy($applicantId);

            return response()->json([
                'success' => true,
                'message' => 'Application deleted!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Application deletion failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Score
    |--------------------------------------------------------------------------
    */

    protected function calculateScore($applicant)
    {
        // Initialize variables to store the total score and total weight
        $totalScore = 0;
        $totalWeight = 0;

        // Retrieve all the score weightings (likely from a database table)
        $weightings = ScoreWeighting::all(); // Fetch all weightings

        // Loop through each weighting to calculate the score
        foreach ($weightings as $weighting) {
            // Check if the score type is 'education_id' and apply custom logic
            if ($weighting->score_type == 'education_id') {
                // Get the education level from the applicant's data
                $educationLevel = $applicant->{$weighting->score_type} ?? 0;

                // Apply custom weight distribution based on the education level
                switch ($educationLevel) {
                    case 1: // Level 1 gets 0% of the weight
                        $percentage = 0;
                        break;
                    case 2:
                    case 3: // Levels 2 and 3 get 15% of the weight
                        $percentage = 0.15;
                        break;
                    case 4: // Level 4 gets 40% of the weight
                        $percentage = 0.40;
                        break;
                    case 5: // Level 5 gets 25% of the weight
                        $percentage = 0.25;
                        break;
                    case 6: // Level 6 gets 20% of the weight
                        $percentage = 0.20;
                        break;
                    default:
                        $percentage = 0;
                        break;
                }

                // Add the weighted score to the total score
                $totalScore += $percentage * $weighting->weight;

            // Check if the score type is 'duration_id' and apply custom logic
            } elseif ($weighting->score_type == 'duration_id') {
                // Get the duration value from the applicant's data
                $durationLevel = $applicant->{$weighting->score_type} ?? 0;

                // Apply custom weight distribution based on the duration level
                switch ($durationLevel) {
                    case 1: // Level 1 gets 0% of the weight
                        $percentage = 0;
                        break;
                    case 2: // Level 2 gets 10% of the weight
                        $percentage = 0.10;
                        break;
                    case 3: // Level 3 gets 15% of the weight
                        $percentage = 0.15;
                        break;
                    case 4: // Level 4 gets 20% of the weight
                        $percentage = 0.20;
                        break;
                    case 5: // Level 5 gets 25% of the weight
                        $percentage = 0.25;
                        break;
                    case 6: // Level 6 gets 30% of the weight
                        $percentage = 0.30;
                        break;
                    default:
                        $percentage = 0;
                        break;
                }

                // Add the weighted score to the total score
                $totalScore += $percentage * $weighting->weight;

            // Check if the score type is 'literacy_score', 'numeracy_score', or 'situational_score'
            } elseif (in_array($weighting->score_type, ['literacy_score', 'numeracy_score', 'situational_score'])) {
                // Get the applicant's score for the current score type
                $scoreValue = $applicant->{$weighting->score_type} ?? 0;
                $maxValue = $weighting->max_value;

                // Calculate the percentage score
                if ($maxValue > 0) {
                    $scorePercentage = ($scoreValue / $maxValue) * 100;

                    // Apply weight based on the percentage score
                    if ($scorePercentage >= 0 && $scorePercentage <= 30) {
                        $percentage = 0; // 0% of the weight for 0-30% score
                    } elseif ($scorePercentage > 30 && $scorePercentage <= 55) {
                        $percentage = 0.05; // 5% of the weight for 31-55% score
                    } elseif ($scorePercentage > 55 && $scorePercentage <= 70) {
                        $percentage = 0.20; // 20% of the weight for 56-70% score
                    } elseif ($scorePercentage > 70 && $scorePercentage <= 85) {
                        $percentage = 0.35; // 35% of the weight for 71-85% score
                    } elseif ($scorePercentage > 85) {
                        $percentage = 0.40; // 40% of the weight for >85% score
                    }

                    // Add the weighted score to the total score
                    $totalScore += $percentage * $weighting->weight;
                }

            // Check if the weighting has a condition (i.e., applies to a specific field and value)
            } elseif (!empty($weighting->condition_field)) {
                // Apply conditional logic: if the applicant's field matches the condition value, use the specified weight
                // Otherwise, use the fallback value as the score
                $scoreValue = $applicant->{$weighting->condition_field} == $weighting->condition_value
                    ? $weighting->weight
                    : $weighting->fallback_value;

                // Add the calculated score value to the total score
                $totalScore += $scoreValue;
            } else {
                // For numeric scoring (without a condition), handle the score calculation based on the score type and max value

                // Get the score value from the applicant's data, using the score type as the field name
                // Default to 0 if no value is present
                $scoreValue = $applicant->{$weighting->score_type} ?? 0;

                // Get the max value from the weighting record (used for percentage calculation)
                $maxValue = $weighting->max_value;

                // If the max value is greater than 0, calculate the percentage score and weight it accordingly
                if ($maxValue > 0) {
                    $percentage = ($scoreValue / $maxValue) * $weighting->weight;
                    $totalScore += $percentage; // Add the weighted score to the total score
                }
            }

            // Add the current weighting's weight to the total weight
            $totalWeight += $weighting->weight;
        }

        // Normalize the total score to a scale of 0 to 5
        // If total weight is greater than 0, divide total score by total weight, then multiply by 5
        // Otherwise, default to normalizing based on 100% scale
        $normalizedScore = $totalWeight > 0 ? ($totalScore / $totalWeight) * 5 : ($totalScore / 100) * 5;

        // Add 3 to the final score
        $finalScore = $normalizedScore + 3;

        // Round the normalized score to 2 decimal places and return it
        return round($finalScore, 2);
    }
}
