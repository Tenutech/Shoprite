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
                'applicant.readLanguages',
                'applicant.speakLanguages',
            ])->findOrFail($userID);

            // Type
            $types = Type::get();

            // Race
            $races = Race::get();

            // Bank
            $banks = Bank::get();

            // Brand
            $brands = Brand::get();

            // Store
            $stores = Store::get();

            // Gender
            $genders = Gender::get();

            // Reason
            $reasons = Reason::get();

            // Language
            $languages = Language::get();

            // Position
            $positions = Position::get();

            // Duration
            $durations = Duration::get();

            // Education
            $educations = Education::get();

            // Applicant
            $applicants = Applicant::get();

            // Transport
            $transports = Transport::get();

            // Disability
            $disabilities = Disability::get();

            // Retrenchment
            $retrenchments = Retrenchment::get();

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

            return view('application', [
                'user' => $user,
                'types' => $types,
                'races' => $races,
                'banks' => $banks,
                'brands' => $brands,
                'stores' => $stores,
                'genders' => $genders,
                'reasons' => $reasons,
                'languages' => $languages,
                'positions' => $positions,
                'durations' => $durations,
                'educations' => $educations,
                'applicants' => $applicants,
                'transports' => $transports,
                'disabilities' => $disabilities,
                'retrenchments' => $retrenchments,
                'literacyQuestions' => $literacyQuestions,
                'numeracyQuestions' => $numeracyQuestions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Score
    |--------------------------------------------------------------------------
    */

    protected function calculateScore($applicant)
    {
        $totalScore = 0;
        $totalWeight = 0;
        $weightings = ScoreWeighting::all(); // Fetch all weightings

        foreach ($weightings as $weighting) {
            // Check if this weighting involves a condition
            if (!empty($weighting->condition_field)) {
                // Handle conditional logic
                $scoreValue = $applicant->{$weighting->condition_field} == $weighting->condition_value ? $weighting->weight : $weighting->fallback_value;
                $totalScore += $scoreValue;
            } else {
                // Handle numeric scoring as before
                $scoreValue = $applicant->{$weighting->score_type} ?? 0;
                $maxValue = $weighting->max_value;
                if ($maxValue > 0) {
                    $percentage = ($scoreValue / $maxValue) * $weighting->weight;
                    $totalScore += $percentage;
                }
            }

            $totalWeight += $weighting->weight;
        }

        // Normalize the score to a scale of 0 to 5
        $normalizedScore = $totalWeight > 0 ? ($totalScore / $totalWeight) * 5 : ($totalScore / 100) * 5;

        return round($normalizedScore, 2); // Round to 2 decimal places
    }

    /*
    |--------------------------------------------------------------------------
    | Application Create
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $commencementDate = Carbon::createFromFormat('d M, Y', $request->commencement);
        $commencementDate = $commencementDate->format('Y-m-d');

        $request->merge(['commencement' => $commencementDate]);

        //Validate Input
        $request->validate([
            'avatar' => ['sometimes', 'image' ,'mimes:jpg,jpeg,png','max:1024'],
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'id_number' => ['required', 'string', 'max:191'],
            'phone' => ['required', 'string', 'max:191'],
            'location' => ['required', 'string'],
            'gender_id' => ['required', 'integer'],
            'race_id' => ['required', 'integer'],
            'email' => ['sometimes', 'nullable', 'string', 'email', 'max:191', 'unique:applicants'],
            'tax_number' => ['sometimes', 'nullable', 'string', 'max:191'],
            'citizen' => ['sometimes', 'required', 'in:Yes,No'],
            'criminal' => ['sometimes', 'required', 'in:Yes,No'],
            'position_id' => ['required', 'integer'],
            'position_specify' => ['sometimes', 'nullable', 'string'],
            'school' => ['required', 'string', 'max:191'],
            'education_id' => ['required', 'integer'],
            'training' => ['sometimes', 'required', 'in:Yes,No'],
            'other_training' => ['sometimes', 'nullable', 'string', 'max:191'],
            'drivers_license_code' => ['sometimes', 'nullable', 'string', 'max:10'],
            'read' => ['required', 'array'],
            'speak' => ['required', 'array'],
            'job_previous' => ['sometimes', 'required', 'in:Yes,No'],
            'reason_id' => ['sometimes', 'integer'],
            'job_leave_specify' => ['sometimes', 'nullable', 'string'],
            'job_business' => ['sometimes', 'nullable', 'string', 'max:191'],
            'job_position' => ['sometimes', 'nullable', 'string'],
            'duration_id' => ['sometimes', 'integer'],
            'job_salary' => ['sometimes', 'nullable', 'string', 'max:191'],
            'job_reference_name' => ['sometimes', 'nullable', 'string', 'max:191'],
            'job_reference_phone' => ['sometimes', 'nullable', 'string', 'max:191'],
            'retrenchment_id' => ['required', 'integer'],
            'job_retrenched_specify' => ['sometimes', 'nullable', 'string'],
            'brand_id' => ['sometimes', 'nullable', 'integer'],
            'previous_job_position_id' => ['sometimes', 'nullable', 'integer'],
            'job_shoprite_position_specify' => ['sometimes', 'nullable', 'string'],
            'job_shoprite_leave' => ['sometimes', 'nullable', 'string'],
            'transport_id' => ['required', 'integer'],
            'transport_specify' => ['sometimes', 'nullable', 'string'],
            'disability_id' => ['required', 'integer'],
            'illness_specify' => ['sometimes', 'nullable', 'string'],
            'commencement' => ['required', 'date', 'date_format:Y-m-d'],
            'type_id' => ['required', 'integer'],
            'application_reason_specify' => ['sometimes', 'nullable', 'string'],
            'relocate' => ['sometimes', 'required', 'in:Yes,No'],
            'relocate_town' => ['sometimes', 'nullable', 'string'],
            'vacancy' => ['sometimes', 'required', 'in:Yes,No'],
            'shift' => ['sometimes', 'required', 'in:Yes,No'],
            'bank_id' => ['required', 'integer'],
            'bank_specify' => ['sometimes', 'nullable', 'string', 'max:191'],
            'bank_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'expected_salary' => ['required', 'numeric', 'min:0']
        ]);

        try {
            //User ID
            $userID = Auth::id();

            //User
            $user = User::find($userID);

            //Form Fields
            if ($request->avatar) {
                $avatar = request()->file('avatar');
                $avatarName = '/images/' . $request->firstname . ' ' . $request->lastname . '-' . time() . '.' . $avatar->getClientOriginalExtension();
                $avatarPath = public_path('/images/');
                $avatar->move($avatarPath, $avatarName);
            } else {
                if ($user && $user->avatar) {
                    $avatarName = $user->avatar;
                } else {
                    $avatarName = '/images/avatar.jpg';
                }
            }
            $firstname = $request->firstname;
            $lastname = $request->lastname;
            $idNumber = $request->id_number;
            $phone = $request->phone;
            $location = $request->location;
            $genderID = $request->gender_id;
            $raceID = $request->race_id;
            $email = $request->email;
            $taxNumber = $request->tax_number;
            $citizen = $request->citizen;
            $criminal = $request->criminal;
            $positionID = $request->position_id;
            $positionSpecify = $request->position_specify;
            $school = $request->school;
            $educationID = $request->education_id;
            $training = $request->training;
            $otherTraining = $request->other_training;
            $driversLicenseCode = $request->drivers_license_code;
            $readLanguages = $request->read;
            $speakLanguages = $request->speak;
            $jobPrevious = $request->job_previous;
            $reasonID = $request->reason_id;
            $jobLeaveSpecify = $request->job_leave_specify;
            $jobBusiness = $request->job_business;
            $jobPosition = $request->job_position;
            $durationID = $request->duration_id;
            $jobSalary = $request->job_salary;
            $jobReferenceName = $request->job_reference_name;
            $jobReferencePhone = $request->job_reference_phone;
            $retrenchmentID = $request->retrenchment_id;
            $jobRetrenchedSpecify = $request->job_retrenched_specify;
            $brandID = $request->brand_id;
            $previousJobPositionID = $request->previous_job_position_id;
            $jobShopritePositionSpecify = $request->job_shoprite_position_specify;
            $jobShopriteLeave = $request->job_shoprite_leave;
            $transportID = $request->transport_id;
            $transportSpecify = $request->transport_specify;
            $disabilityID = $request->disability_id;
            $illnessSpecify = $request->illness_specify;
            $commencement = $request->commencement;
            $typeID = $request->type_id;
            $applicationReasonSpecify = $request->application_reason_specify;
            $relocate = $request->relocate;
            $relocateTown = $request->relocate_town;
            $vacancy = $request->vacancy;
            $shift = $request->shift;
            $bankID = $request->bank_id;
            $bankSpecify = $request->bank_specify;
            $bankNumber = $request->bank_number;
            $expectedSalary = $request->expected_salary;
            $literacyAnswers = $request->literacy_answers;
            $numeracyAnswers = $request->numeracy_answers;

            $literacyQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['literacy']);
            })->get();

            $numeracyQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['numeracy']);
            })->get();

            $literacyScore = 0;
            $literacyQuestionsCount = $literacyQuestions->count();

            $numeracyScore = 0;
            $numeracyQuestionsCount = $numeracyQuestions->count();

            foreach ($literacyQuestions as $question) {
                if (isset($literacyAnswers[$question->id]) && $literacyAnswers[$question->id] == $question->answer) {
                    $literacyScore++;
                }
            }

            foreach ($numeracyQuestions as $question) {
                if (isset($numeracyAnswers[$question->id]) && $numeracyAnswers[$question->id] == $question->answer) {
                    $numeracyScore++;
                }
            }

            // Get the 'complete' state ID
            $completeStateID = State::where('code', 'complete')->value('id');

            DB::beginTransaction();

            // Applicant Create
            $applicant = Applicant::create([
                'phone' => $phone,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'id_number' => $idNumber,
                'location' => $location,
                'contact_number' => $phone,
                'additional_contact_number' => $phone,
                'gender_id' => $genderID,
                'race_id' => $raceID,
                'has_email' => $email ? 'Yes' : 'No',
                'email' => $email,
                'has_tax' => $taxNumber ? 'Yes' : 'No',
                'tax_number' => $taxNumber,
                'citizen' => $citizen,
                'foreign_national' => $citizen == 'Yes' ? 'No' : 'Yes',
                'criminal' => $criminal,
                'avatar' => $avatarName,
                'position_id' => $positionID,
                'position_specify' => $positionSpecify,
                'school' => $school,
                'education_id' => $educationID,
                'training' => $training,
                'other_training' => $otherTraining,
                'drivers_license' => $driversLicenseCode ? 'Yes' : 'No',
                'drivers_license_code' => $driversLicenseCode,
                'read_languages' => $readLanguages,
                'speak_languages' => $speakLanguages,
                'job_previous' => $jobPrevious,
                'reason_id' => $reasonID,
                'job_leave_specify' => $jobLeaveSpecify,
                'job_business' => $jobBusiness,
                'job_position' => $jobPosition,
                'duration_id' => $durationID,
                'job_salary' => $jobSalary,
                'job_reference_name' => $jobReferenceName,
                'job_reference_phone' => $jobReferencePhone,
                'retrenchment_id' => $retrenchmentID,
                'job_retrenched_specify' => $jobRetrenchedSpecify,
                'brand_id' => $brandID,
                'previous_job_position_id' => $previousJobPositionID,
                'job_shoprite_position_specify' => $jobShopritePositionSpecify,
                'job_shoprite_leave' => $jobShopriteLeave,
                'transport_id' => $transportID,
                'transport_specify' => $transportSpecify,
                'disability_id' => $disabilityID,
                'illness_specify' => $illnessSpecify,
                'commencement' => $commencement,
                'type_id' => $typeID,
                'application_reason_specify' => $applicationReasonSpecify,
                'relocate' => $relocate,
                'relocate_town' => $relocateTown,
                'vacancy' => $vacancy,
                'shift' => $shift,
                'has_bank_account' => $bankID ? 'Yes' : 'No',
                'bank_id' => $bankID,
                'bank_specify' => $bankSpecify,
                'bank_number' => $bankNumber,
                'expected_salary' => $expectedSalary,
                'literacy_score' => $literacyScore,
                'literacy_questions' => $literacyQuestionsCount,
                'literacy' => "{$literacyScore}/{$literacyQuestionsCount}",
                'numeracy_score' => $numeracyScore,
                'numeracy_questions' => $numeracyQuestionsCount,
                'numeracy' => "{$numeracyScore}/{$numeracyQuestionsCount}",
                'role_id' => 8,
                'state_id' => $completeStateID,
            ]);

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

            // Read Languages
            if ($request->has('read')) {
                $read = array_fill_keys($request->read, ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
                $applicant->readLanguages()->sync($read);
            }

            // Speak Languages
            if ($request->has('speak')) {
                $speak = array_fill_keys($request->speak, ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
                $applicant->speakLanguages()->sync($speak);
            }

            // Calculate the score for the applicant
            $score = $this->calculateScore($applicant);

            // Update the applicant with the calculated score
            $applicant->score = $score;
            $applicant->save();

            //Update user applicant id
            if ($applicant) {
                $user = Auth::user();
                $user->applicant_id = $applicant->id;
                $user->save();
            }

            // Dispatch the job for the applicant, with no need to update a user
            ProcessUserIdNumber::dispatch(null, $applicant->id);

            // If a new applicant was created, then create a notification
            if ($applicant->wasRecentlyCreated) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $userID;
                $notification->causer_id = Auth::id();
                $notification->subject()->associate($applicant);
                $notification->type_id = 1;
                $notification->notification = "Submitted application 🔔";
                $notification->read = "No";
                $notification->save();
            }

            DB::commit();

            $encryptedID = Crypt::encryptString($applicant->id);

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully!',
                'applicant' => $applicant,
                'encrypted_id' => $encryptedID
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit application!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Application Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $applicantID = Crypt::decryptString($request->id);

        $commencementDate = Carbon::createFromFormat('d M, Y', $request->commencement);
        $commencementDate = $commencementDate->format('Y-m-d');

        $request->merge(['commencement' => $commencementDate]);

        //Validate Input
        $request->validate([
            'avatar' => ['sometimes', 'image' ,'mimes:jpg,jpeg,png','max:1024'],
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'id_number' => ['required', 'string', 'max:191'],
            'phone' => ['required', 'string', 'max:191'],
            'location' => ['required', 'string'],
            'gender_id' => ['required', 'integer'],
            'race_id' => ['required', 'integer'],
            'email' => ['sometimes','string','email','max:191',Rule::unique('applicants')->ignore($applicantID)],
            'tax_number' => ['sometimes', 'nullable', 'string', 'max:191'],
            'citizen' => ['sometimes', 'required', 'in:Yes,No'],
            'criminal' => ['sometimes', 'required', 'in:Yes,No'],
            'position_id' => ['required', 'integer'],
            'position_specify' => ['sometimes', 'nullable', 'string'],
            'school' => ['required', 'string', 'max:191'],
            'education_id' => ['required', 'integer'],
            'training' => ['sometimes', 'required', 'in:Yes,No'],
            'other_training' => ['sometimes', 'nullable', 'string', 'max:191'],
            'drivers_license_code' => ['sometimes', 'nullable', 'string', 'max:10'],
            'read' => ['required', 'array'],
            'speak' => ['required', 'array'],
            'job_previous' => ['sometimes', 'required', 'in:Yes,No'],
            'reason_id' => ['sometimes', 'integer'],
            'job_leave_specify' => ['sometimes', 'nullable', 'string'],
            'job_business' => ['sometimes', 'nullable', 'string', 'max:191'],
            'job_position' => ['sometimes', 'nullable', 'string'],
            'duration_id' => ['sometimes', 'integer'],
            'job_salary' => ['sometimes', 'nullable', 'string', 'max:191'],
            'job_reference_name' => ['sometimes', 'nullable', 'string', 'max:191'],
            'job_reference_phone' => ['sometimes', 'nullable', 'string', 'max:191'],
            'job_retrenched_specify' => ['sometimes', 'nullable', 'string'],
            'brand_id' => ['sometimes', 'nullable', 'integer'],
            'previous_job_position_id' => ['sometimes', 'nullable', 'integer'],
            'job_shoprite_position_specify' => ['sometimes', 'nullable', 'string'],
            'job_shoprite_leave' => ['sometimes', 'nullable', 'string'],
            'transport_id' => ['required', 'integer'],
            'transport_specify' => ['sometimes', 'nullable', 'string'],
            'disability_id' => ['required', 'integer'],
            'illness_specify' => ['sometimes', 'nullable', 'string'],
            'commencement' => ['required', 'date', 'date_format:Y-m-d'],
            'type_id' => ['required', 'integer'],
            'application_reason_specify' => ['sometimes', 'nullable', 'string'],
            'relocate' => ['sometimes', 'required', 'in:Yes,No'],
            'relocate_town' => ['sometimes', 'nullable', 'string'],
            'vacancy' => ['sometimes', 'required', 'in:Yes,No'],
            'shift' => ['sometimes', 'required', 'in:Yes,No'],
            'bank_id' => ['required', 'integer'],
            'bank_specify' => ['sometimes', 'nullable', 'string', 'max:191'],
            'bank_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'expected_salary' => ['required', 'numeric', 'min:0']
        ]);

        try {
            //User ID
            $userID = Auth::id();

            //User
            $user = User::find($userID);

            //Applicant
            $applicant = Applicant::findOrFail($applicantID);

            //Form Fields
            if ($request->hasFile('avatar')) {
                $avatar = request()->file('avatar');
                $avatarName = '/images/' . $request->firstname . ' ' . $request->lastname . '-' . time() . '.' . $avatar->getClientOriginalExtension();
                $avatarPath = public_path('/images/');
                $avatar->move($avatarPath, $avatarName);
            } else {
                if ($user && $user->avatar) {
                    $avatarName = $user->avatar;
                } else {
                    $avatarName = '/images/avatar.jpg';
                }
            }
            $firstname = $request->firstname;
            $lastname = $request->lastname;
            $idNumber = $request->id_number;
            $phone = $request->phone;
            $location = $request->location;
            $genderID = $request->gender_id;
            $raceID = $request->race_id;
            $email = $request->email;
            $taxNumber = $request->tax_number;
            $citizen = $request->citizen;
            $criminal = $request->criminal;
            $positionID = $request->position_id;
            $positionSpecify = $request->position_specify;
            $school = $request->school;
            $educationID = $request->education_id;
            $training = $request->training;
            $otherTraining = $request->other_training;
            $driversLicenseCode = $request->drivers_license_code;
            $readLanguages = $request->read;
            $speakLanguages = $request->speak;
            $jobPrevious = $request->job_previous;
            $reasonID = $request->reason_id;
            $jobLeaveSpecify = $request->job_leave_specify;
            $jobBusiness = $request->job_business;
            $jobPosition = $request->job_position;
            $durationID = $request->duration_id;
            $jobSalary = $request->job_salary;
            $jobReferenceName = $request->job_reference_name;
            $jobReferencePhone = $request->job_reference_phone;
            $retrenchmentID = $request->retrenchment_id;
            $jobRetrenchedSpecify = $request->job_retrenched_specify;
            $brandID = $request->brand_id;
            $previousJobPositionID = $request->previous_job_position_id;
            $jobShopritePositionSpecify = $request->job_shoprite_position_specify;
            $jobShopriteLeave = $request->job_shoprite_leave;
            $transportID = $request->transport_id;
            $transportSpecify = $request->transport_specify;
            $disabilityID = $request->disability_id;
            $illnessSpecify = $request->illness_specify;
            $commencement = $request->commencement;
            $typeID = $request->type_id;
            $applicationReasonSpecify = $request->application_reason_specify;
            $relocate = $request->relocate;
            $relocateTown = $request->relocate_town;
            $vacancy = $request->vacancy;
            $shift = $request->shift;
            $bankID = $request->bank_id;
            $bankSpecify = $request->bank_specify;
            $bankNumber = $request->bank_number;
            $expectedSalary = $request->expected_salary;
            $literacyAnswers = $request->literacy_answers;
            $numeracyAnswers = $request->numeracy_answers;

            $literacyQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['literacy']);
            })->get();

            $numeracyQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['numeracy']);
            })->get();

            $literacyScore = 0;
            $literacyQuestionsCount = $literacyQuestions->count();

            $numeracyScore = 0;
            $numeracyQuestionsCount = $numeracyQuestions->count();

            foreach ($literacyQuestions as $question) {
                if (isset($literacyAnswers[$question->id]) && $literacyAnswers[$question->id] == $question->answer) {
                    $literacyScore++;
                }
            }

            foreach ($numeracyQuestions as $question) {
                if (isset($numeracyAnswers[$question->id]) && $numeracyAnswers[$question->id] == $question->answer) {
                    $numeracyScore++;
                }
            }

            // Get the 'complete' state ID
            $completeStateID = State::where('code', 'complete')->value('id');

            DB::beginTransaction();

            // Applicant Create
            $applicant->update([
                'phone' => $phone,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'id_number' => $idNumber,
                'location' => $location,
                'contact_number' => $phone,
                'additional_contact_number' => $phone,
                'gender_id' => $genderID,
                'race_id' => $raceID,
                'has_email' => $email ? 'Yes' : 'No',
                'email' => $email,
                'has_tax' => $taxNumber ? 'Yes' : 'No',
                'tax_number' => $taxNumber,
                'citizen' => $citizen,
                'foreign_national' => $citizen == 'Yes' ? 'No' : 'Yes',
                'criminal' => $criminal,
                'avatar' => $avatarName,
                'position_id' => $positionID,
                'position_specify' => $positionSpecify,
                'school' => $school,
                'education_id' => $educationID,
                'training' => $training,
                'other_training' => $otherTraining,
                'drivers_license' => $driversLicenseCode ? 'Yes' : 'No',
                'drivers_license_code' => $driversLicenseCode,
                'read_languages' => $readLanguages,
                'speak_languages' => $speakLanguages,
                'job_previous' => $jobPrevious,
                'reason_id' => $reasonID,
                'job_leave_specify' => $jobLeaveSpecify,
                'job_business' => $jobBusiness,
                'job_position' => $jobPosition,
                'duration_id' => $durationID,
                'job_salary' => $jobSalary,
                'job_reference_name' => $jobReferenceName,
                'job_reference_phone' => $jobReferencePhone,
                'retrenchment_id' => $retrenchmentID,
                'job_retrenched_specify' => $jobRetrenchedSpecify,
                'brand_id' => $brandID,
                'previous_job_position_id' => $previousJobPositionID,
                'job_shoprite_position_specify' => $jobShopritePositionSpecify,
                'job_shoprite_leave' => $jobShopriteLeave,
                'transport_id' => $transportID,
                'transport_specify' => $transportSpecify,
                'disability_id' => $disabilityID,
                'illness_specify' => $illnessSpecify,
                'commencement' => $commencement,
                'type_id' => $typeID,
                'application_reason_specify' => $applicationReasonSpecify,
                'relocate' => $relocate,
                'relocate_town' => $relocateTown,
                'vacancy' => $vacancy,
                'shift' => $shift,
                'has_bank_account' => $bankID ? 'Yes' : 'No',
                'bank_id' => $bankID,
                'bank_specify' => $bankSpecify,
                'bank_number' => $bankNumber,
                'expected_salary' => $expectedSalary,
                'literacy_score' => $literacyScore,
                'literacy_questions' => $literacyQuestionsCount,
                'literacy' => "{$literacyScore}/{$literacyQuestionsCount}",
                'numeracy_score' => $numeracyScore,
                'numeracy_questions' => $numeracyQuestionsCount,
                'numeracy' => "{$numeracyScore}/{$numeracyQuestionsCount}",
                'role_id' => 8,
                'state_id' => $completeStateID,
            ]);

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

            // Read Languages
            if ($request->has('read')) {
                $read = array_fill_keys($request->read, ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
                $applicant->readLanguages()->sync($read);
            }

            // Speak Languages
            if ($request->has('speak')) {
                $speak = array_fill_keys($request->speak, ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
                $applicant->speakLanguages()->sync($speak);
            }

            // Calculate the score for the applicant
            $score = $this->calculateScore($applicant);

            // Update the applicant with the calculated score
            $applicant->score = $score;
            $applicant->save();

            //Update user applicant id
            if ($applicant) {
                $user = Auth::user();
                $user->applicant_id = $applicant->id;
                $user->save();
            }

            // If a new applicant was created, then create a notification
            if ($applicant->wasRecentlyCreated) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $userID;
                $notification->causer_id = Auth::id();
                $notification->subject()->associate($applicant);
                $notification->type_id = 1;
                $notification->notification = "Updated your application 🔔";
                $notification->read = "No";
                $notification->save();
            }

            DB::commit();

            $encryptedID = Crypt::encryptString($applicant->id);

            return response()->json([
                'success' => true,
                'message' => 'Application updated successfully!',
                'applicant' => $applicant,
                'encrypted_id' => $encryptedID
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update application!',
                'error' => $e->getMessage()
            ], 400);
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
            $applicantID = Crypt::decryptString($id);

            //Delete Application
            Applicant::destroy($applicantID);

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
}
