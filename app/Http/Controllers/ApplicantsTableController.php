<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Race;
use App\Models\Brand;
use App\Models\State;
use App\Models\Gender;
use App\Models\Document;
use App\Models\Duration;
use App\Models\Education;
use App\Models\Applicant;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\ChatTemplate;
use App\Models\ApplicantType;
use App\Models\ScoreWeighting;
use App\Jobs\SendIdNumberToSap;
use App\Jobs\ProcessUserIdNumber;
use Illuminate\Support\Facades\DB;
use App\Services\GoogleMapsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\UpdateApplicantRequest;

class ApplicantsTableController extends Controller
{
    private GoogleMapsService $googleMapsService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(GoogleMapsService $googleMapsService)
    {
        $this->middleware(['auth', 'verified']);
        $this->googleMapsService = $googleMapsService;
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
        if (view()->exists('admin.applicants-table')) {
            //Applicants
            $applicants = Applicant::with([
                'town',
                'gender',
                'race',
                'education',
                'duration',
                'brands',
                'role',
                'state',
                'vacancyFill',
                'interviews',
                'applicantType'
            ])
            ->orderby('firstname')
            ->orderby('lastname')
            ->get();

            //Genders
            $genders = Gender::all();

            //Durations
            $durations = Duration::get();

            // Education
            $educations = Education::where('id', '!=', 3)->get();

            // Brand
            $brands = Brand::whereIn('id', [2, 5, 6])->get();

            // Races
            $races = Race::get();

            // States
            $states = State::get();

            // Applicant Types
            $applicantTypes = ApplicantType::get();

            return view('admin/applicants-table', [
                'applicants' => $applicants,
                'genders' => $genders,
                'durations' => $durations,
                'educations' => $educations,
                'brands' => $brands,
                'races' => $races,
                'states' => $states,
                'applicantTypes' => $applicantTypes
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        $currentLocation = '';
        $latitude = '';
        $longitude = '';

        try {
            $applicantID = Crypt::decryptString($id);

            $applicant = applicant::with([
                'town',
                'gender',
                'race',
                'education',
                'duration',
                'brands',
                'role',
                'state',
                'interviews'
            ])->findOrFail($applicantID);

            if ($applicant->location !== null) {
                $currentLocation = $this->googleMapsService->geocodeAddress($applicant->location);
                $latitude = $currentLocation['latitude'];
                $longitude = $currentLocation['longitude'];
            }

            return response()->json([
                'applicant' => $applicant,
                'currentLatitude' => $latitude,
                'currentLongitude' => $longitude,
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
    | Application Update
    |--------------------------------------------------------------------------
    */

    public function update(UpdateApplicantRequest $request)
    {
        //Applicant ID
        $applicantId = Crypt::decryptString($request->field_id);

        //Validate Input
        $request->validated();

        try {
            // Get the current authenticated user
            $userId = Auth::id();
            $user = User::find($userId);

            //Applicant
            $applicant = Applicant::findOrFail($applicantId);

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
            $brandId = $request->brand_id;
            $disability = $request->disability;
            $state_id = $request->state_id;

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
                'brand_id' => $brandId,
                'location_type' => 'Address',
                'location' => $location,
                'coordinates' => $coordinates,
                'has_email' => $email ? 'Yes' : 'No', // Determine if email was provided
                'email' => $email,
                'disability' => $disability,
                'role_id' => 8,
                'state_id' => $state_id,
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

            User::destroy($decryptedIds);

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
