<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\State;
use App\Models\Vacancy;
use App\Models\Contract;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\ChatTemplate;
use App\Models\Notification;
use App\Jobs\SendWhatsAppMessage;
use App\Jobs\SendWhatsAppFile;
use App\Jobs\UpdateApplicantData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;

class InterviewController extends Controller
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
    | Interview Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('interviews')) {
            //UserID
            $userID = Auth::id();

            //User
            $user = User::findorfail($userID);

            //Interviews
            $interviews = Interview::with([
                'applicant',
                'interviewer',
                'vacancy'
            ])
            ->when($user->role_id > 6, function ($query) use ($user) {
                // If user role is greater than 6, get interviews where the user is the applicant
                return $query->where('applicant_id', $user->applicant_id);
            }, function ($query) use ($userID) {
                // If user role is 6 or less, get interviews where the user is the interviewer
                return $query->where('interviewer_id', $userID);
            })
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('updated_at', 'desc') 
            ->get();

            return view('interviews', [
                'user' => $user,
                'interviews' => $interviews
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Create
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        // Decrypt Vacancy ID with error handling
        try {
            $vacancyID = Crypt::decryptString($request->input('vacancy_id'));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Payload',
                'error' => $e->getMessage()
            ], 400);
        }

        // Format the date input
        $interviewDate = Carbon::createFromFormat('d M, Y', $request->date);
        $interviewDateFormatted = $interviewDate->format('Y-m-d');

        // Format the start time input
        $startTime = Carbon::createFromFormat('H:i', $request->start_time);
        $startTimeFormatted = $startTime->format('H:i');

        // Format the end time input
        $endTime = Carbon::createFromFormat('H:i', $request->end_time);
        $endTimeFormatted = $endTime->format('H:i');

        // Merge the formatted date and times back into the request
        $request->merge([
            'vacancy_id_decrypted' => $vacancyID,
            'date' => $interviewDateFormatted,
            'start_time' => $startTimeFormatted,
            'end_time' => $endTimeFormatted,
        ]);

        // Get the current date and time
        $currentDateTime = Carbon::now();

        // Validate the date request data
        $validatedData = $request->validate([
            'vacancy_id_decrypted' => 'required|int|exists:vacancies,id',
            'applicants' => 'required|array',
            'date' => ['required', 'date', function ($attribute, $value, $fail) use ($interviewDate, $currentDateTime) {
                // Ensure the interview date is after the current date
                if ($interviewDate->lt($currentDateTime->startOfDay())) {
                    $fail('The interview date must be in the future.');
                }
            }],
            'start_time' => ['required', 'date_format:H:i', function ($attribute, $value, $fail) use ($startTime, $interviewDate, $currentDateTime) {
                // Ensure the start time is after the current time if the date is today
                if ($interviewDate->isSameDay($currentDateTime) && $startTime->lt($currentDateTime)) {
                    $fail('The interview start time must be in the future.');
                }
            }],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time', function ($attribute, $value, $fail) use ($startTime, $endTime) {
                // Ensure the end time is at least 30 minutes after the start time and no more than 1 hour
                $duration = $startTime->diffInMinutes($endTime);
                if ($duration < 30 || $duration > 60) {
                    $fail('The interview end time must be between 30 minutes and 1 hour after the start time.');
                }
            }],
            'location' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Get the state_id for 'scheduled_start'
        $scheduledStateId = State::where('code', 'schedule_start')->value('id');

        try {
            // Start a transaction
            DB::beginTransaction();

            // Initialize interview results
            $interviewResults = [];

            // Loop through each applicant and create an interview or reschedule an existing one
            foreach ($validatedData['applicants'] as $applicantID) {
                $result = $this->scheduleInterviewForApplicant($applicantID, $validatedData, $scheduledStateId);
                $interviewResults[] = $result;
            }

            // Commit the transaction
            DB::commit();

            // Return an error respons
            return response()->json([
                'success' => true,
                'message' => 'Interviews scheduled successfully.',
                'interviews' => $interviewResults,
                'date' => $interviewDate->format('d M'),
                'time' => $startTime->format('H:i')
            ]);
        } catch (\Exception $e) {
            // An error occurred; cancel the transaction
            DB::rollBack();  

            // Return an error response
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Schedule
    |--------------------------------------------------------------------------
    */

    private function scheduleInterviewForApplicant($applicantID, $validatedData, $scheduledStateId)
    {
        // Retrieve the applicant
        $applicant = Applicant::findOrFail($applicantID);

        // Check if the applicant has already been appointed
        if ($applicant->appointed_id) {
            // If appointed, return a 400 error with the appropriate message
            throw new \Exception("{$applicant->firstname} {$applicant->lastname} has already been appointed.");
        }

        // Check if the applicant already has an interview for the same vacancy and is completed with a score
        $completedInterview = Interview::where('applicant_id', $applicantID)
            ->where('vacancy_id', $validatedData['vacancy_id_decrypted'])
            ->where('status', 'Completed') // Assuming 'Appointed' is the status string
            ->whereNotNull('score')        // Check if the interview has a score
            ->first();

        if ($completedInterview) {
            // If such an interview exists, return a response with a 400 error and stop further processing
            throw new \Exception("{$applicant->firstname} {$applicant->lastname} has already been interviewed for this position.");
        }

        //User ID
        $userID = Auth::id();

        // Check if an interview already exists for the same applicant, user, and vacancy
        $existingInterview = Interview::where('applicant_id', $applicantID)
            ->where('interviewer_id', Auth::id())
            ->where('vacancy_id', $validatedData['vacancy_id_decrypted'])
            ->first();

        if ($existingInterview) {
            // Update the existing interview with the reschedule information
            $existingInterview->update([
                'status' => 'Reschedule',
                'reschedule_by' => 'Manager',
                'reschedule_date' => $validatedData['date'] . ' ' . $validatedData['start_time'], // Combine date and time
            ]);

            // If a new interview was updated, then create a notification
            if ($existingInterview->wasChanged() && $applicant->user) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $existingInterview->applicant && $existingInterview->applicant->user ? $existingInterview->applicant->user->id : null;
                $notification->causer_id = $userID;
                $notification->subject()->associate($existingInterview);
                $notification->type_id = 1;
                $notification->notification = "Requested to reschedule 📅";
                $notification->read = "No";
                $notification->save();
            }

            // If scheduled state exists then update applicant
            if ($scheduledStateId) {
                // Update the applicant's state
                $applicant->update(['state_id' => $scheduledStateId]);
            }
    
            // Prepare a WhatsApp message
            $whatsappMessage = "You have a request to reschedule your interview to " .
                Carbon::parse($existingInterview->reschedule_date)->format('d M Y \a\t H:i') .
                ". Would you like to view the details?";
    
            // Define the message type and template
            $type = 'template';
            $template = 'interview_reschedule_view';
    
            // Prepare the variables for the WhatsApp template
            $variables = [
                $applicant->firstname ?: 'N/A',  // Applicant's first name
                Carbon::parse($existingInterview->reschedule_date)->format('d M Y') ?: 'N/A', // Rescheduled date
                Carbon::parse($existingInterview->reschedule_date)->format('H:i') ?: 'N/A', // Rescheduled time
            ];
    
            // Dispatch WhatsApp message
            SendWhatsAppMessage::dispatch($applicant, $whatsappMessage, $type, $template, $variables);

            //Return Result
            return ['applicant' => $applicant->id, 'status' => 'Reschedule', 'interview' => $existingInterview];
        } else {
            // Assuming your Interview model has the appropriate fillable attributes set
            $interview = Interview::create([
                'applicant_id' => $applicantID,
                'interviewer_id' => $userID,
                'vacancy_id' => $validatedData['vacancy_id_decrypted'],
                'scheduled_date' => $validatedData['date'],
                'start_time' => $validatedData['start_time'],
                'end_time' => $validatedData['end_time'],
                'location' => $validatedData['location'],
                'notes' => $validatedData['notes'] ?? null,
                'status' => 'Scheduled',
            ]);

            // If scheduled state exists then update applicant
            if ($scheduledStateId) {
                // Update the applicant's state
                $applicant->update(['state_id' => $scheduledStateId]);
            }

            $user = User::where('applicant_id', $applicantID)->first();

            // If a new interview was updated, then create a notification
            if ($user && $interview->wasRecentlyCreated) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $user->id;
                $notification->causer_id = $userID;
                $notification->subject()->associate($interview);
                $notification->type_id = 1;
                $notification->notification = "Interview Scheduled 📅";
                $notification->read = "No";
                $notification->save();
            }

            //Fetch the state messages
            $message = "You have been scheduled for an interview. 📆";

            // Define the message type and template
            $type = 'template';
            $template = 'interview_view';

            // Prepare the variables for the WhatsApp template
            $variables = [
                $applicant->firstname ?: 'N/A'
            ];

            // Dispatch WhatsApp message
            SendWhatsAppMessage::dispatch($applicant, $message, $type, $template, $variables);

            //Return Result
            return ['applicant' => $applicant->id, 'status' => 'Scheduled', 'interview' => $interview];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Confirm
    |--------------------------------------------------------------------------
    */

    public function confirm(Request $request)
    {
        try {
            // User ID
            $userID = Auth::id();

            //User
            $user = User::findOrFail($userID);

            //Interview
            $interviewID = Crypt::decryptString($request->id);
            $interview = Interview::findOrFail($interviewID);

            // Merge decrypted IDs back into the request for validation purposes
            $request->merge([
                'interview_id' => $interview->id,
            ]);

            // Validate that the request has an interview_id
            $request->validate([
                'interview_id' => 'required|exists:interviews,id'
            ]);

            // Check if the interview status is one of 'Scheduled', 'Confirmed', or 'Reschedule'
            if (!in_array($interview->status, ['Scheduled', 'Confirmed', 'Reschedule'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interview can only be confirmed if it is in Scheduled or Reschedule status.',
                ], 400);
            }

            //Applicant
            $applicant = Applicant::find($interview->applicant_id);

            // Check if the interview exists, the user is the interviewer, and the status is 'Reschedule'.
            if ($interview && $interview->interviewer_id == $userID && $interview->status == 'Reschedule'  && $interview->reschedule_by == 'Applicant') {
                // Parse the reschedule_date
                $rescheduleDateTime = new \Carbon\Carbon($interview->reschedule_date);

                // Set the scheduled_date and start_time based on the reschedule_date
                $scheduledDate = $rescheduleDateTime->format('Y-m-d'); // Extract only the date (e.g., '2024-10-05')
                $startTime = $rescheduleDateTime->format('H:i:s'); // Extract only the time (e.g., '15:00:00')

                // Set the end_time to 1 hour after the start_time
                $endTime = $rescheduleDateTime->addHour()->format('H:i:s');

                // Update the interview with the new scheduled_date, start_time, and end_time
                $interview->update([
                    'status' => 'Confirmed',
                    'scheduled_date' => $scheduledDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'reschedule_date' => null,
                    'reschedule_by' => null
                ]);

                // If a new interview was updated, then create a notification
                if ($interview->wasChanged() && $interview->applicant && $interview->applicant->user) {
                    // Create Notification
                    $notification = new Notification();
                    $notification->user_id = $interview->applicant && $interview->applicant->user ? $interview->applicant->user->id : null;
                    $notification->causer_id = $userID;
                    $notification->subject()->associate($interview);
                    $notification->type_id = 1;
                    $notification->notification = "Confirmed your interview request ✅";
                    $notification->read = "No";
                    $notification->save();
                }

                // Prepare a WhatsApp message informing the applicant about the confirmed interview
                $whatsappMessage = "Dear " . ($interview->applicant->firstname ?: 'N/A') . ", your interview for the " .
                    optional($interview->vacancy->position)->name ?: 'N/A' . " position at " .
                    optional($interview->vacancy->store->brand)->name ?: 'N/A' . " (" .
                    optional($interview->vacancy->store->town)->name ?: 'N/A' . "), scheduled for " .
                    optional($interview->scheduled_date)->format('d M Y') . " at " .
                    optional($interview->start_time)->format('H:i') . ", has been confirmed.
                    We look forward to seeing you. If you have any questions, feel free to reach out.";

                // Define the message type
                $type = 'template';

                // Define the template for a confirmed interview
                $template = 'interview_confirmed';

                // Prepare the variables (these values will be injected into the template)
                $variables = [
                    $interview->applicant->firstname ?: 'N/A',  // Applicant's first name
                    optional($interview->vacancy->position)->name ?: 'N/A',  // Position name
                    optional($interview->vacancy->store->brand)->name ?: 'N/A',  // Store brand name
                    optional($interview->vacancy->store->town)->name ?: 'N/A',  // Store town name
                    optional($interview->scheduled_date)->format('d M Y') ?: 'N/A', // Interview date
                    optional($interview->start_time)->format('H:i') ?: 'N/A', // Interview start time
                    $interview->location ?: 'N/A', // Interview location
                    $interview->notes ?: 'N/A' // Interview notes
                ];

                // Dispatch a job to send the WhatsApp message
                SendWhatsAppMessage::dispatch($applicant, $whatsappMessage, $type, $template, $variables);
            // Additional check if the interview exists, the user is the applicant, and the status is 'Reschedule'.
            } else if ($interview && $interview->applicant_id == $user->applicant_id && $interview->status == 'Reschedule'  && $interview->reschedule_by == 'Manager') {
                // Parse the reschedule_date
                $rescheduleDateTime = new \Carbon\Carbon($interview->reschedule_date);

                // Set the scheduled_date and start_time based on the reschedule_date
                $scheduledDate = $rescheduleDateTime->format('Y-m-d'); // Extract only the date (e.g., '2024-10-05')
                $startTime = $rescheduleDateTime->format('H:i:s'); // Extract only the time (e.g., '15:00:00')

                // Set the end_time to 1 hour after the start_time
                $endTime = $rescheduleDateTime->addHour()->format('H:i:s');

                // Update the interview with the new scheduled_date, start_time, and end_time
                $interview->update([
                    'status' => 'Confirmed',
                    'scheduled_date' => $scheduledDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'reschedule_date' => null,
                    'reschedule_by' => null
                ]);

                // If a new interview was updated, then create a notification
                if ($interview->wasChanged()) {
                    // Create Notification
                    $notification = new Notification();
                    $notification->user_id = $interview->interviewer_id;
                    $notification->causer_id = $userID;
                    $notification->subject()->associate($interview);
                    $notification->type_id = 1;
                    $notification->notification = "Confirmed your interview request ✅";
                    $notification->read = "No";
                    $notification->save();
                }
            // Additional check if the current user is the applicant, and confirm the interview in that case
            } else if ($interview && $interview->applicant_id == $user->applicant_id && $interview->status == 'Manager') {
                //Interview Update
                $interview->update([
                    'status' => 'Confirmed'
                ]);

                // If a new interview was updated, then create a notification
                if ($interview->wasChanged()) {
                    // Create Notification
                    $notification = new Notification();
                    $notification->user_id = $interview->interviewer_id;
                    $notification->causer_id = $userID;
                    $notification->subject()->associate($interview);
                    $notification->type_id = 1;
                    $notification->notification = "Confirmed your interview request ✅";
                    $notification->read = "No";
                    $notification->save();
                }
            } else {
                // If the interview could not be confirmed due to failing the conditions, return a failure response
                return response()->json([
                    'success' => false,
                    'message' => 'Could not confirm interview!',
                ], 400);
            }

            // Encrypt the interview ID again to be used in the response
            $encryptedID = Crypt::encryptString($interviewID);

             // Return a success response
            return response()->json([
                'success' => true,
                'interview' => $interview,
                'encryptedID' => $encryptedID,
                'message' => 'Interview confirmed!',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation failure
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Return a failure response with the error message
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm interview.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Decline
    |--------------------------------------------------------------------------
    */

    public function decline(Request $request)
    {
        try {
            // User ID
            $userID = Auth::id();

            //User
            $user = User::findOrFail($userID);

            // Check if the user is allowed to decline the interview (only applicants)
            if ($user->role_id <= 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only applicants can decline interviews, please use the cancel option.',
                ], 400);
            }


            //Interview
            $interviewID = Crypt::decryptString($request->id);
            $interview = Interview::where('id', $interviewID)
                                  ->where('applicant_id', $user->applicant_id) // Ensure the applicant is the current user
                                  ->firstOrFail();

            // Merge decrypted IDs back into the request for validation purposes
            $request->merge([
                'interview_id' => $interview->id,
            ]);

            // Validate that the request has an interview_id
            $request->validate([
                'interview_id' => 'required|exists:interviews,id'
            ]);

            // Check if the interview status is one of 'Scheduled', 'Confirmed', or 'Reschedule'
            if (!in_array($interview->status, ['Scheduled', 'Confirmed', 'Reschedule'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interview can only be declined if it is in Scheduled, Confirmed, or Reschedule status.',
                ], 400);
            }

            //Interview Update
            $interview->update([
                'status' => 'Declined'
            ]);

            // If a new interview was updated, then create a notification
            if ($interview->wasChanged()) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $interview->interviewer_id;
                $notification->causer_id = $userID;
                $notification->subject()->associate($interview);
                $notification->type_id = 1;
                $notification->notification = "Declined your interview request 🚫";
                $notification->read = "No";
                $notification->save();
            }

            return response()->json([
                'success' => true,
                'interview' => $interview,
                'message' => 'Interview declined!',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation failure
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to decline interview.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Reschedule
    |--------------------------------------------------------------------------
    */

    public function reschedule(Request $request)
    {
        try {
            // User ID
            $userID = Auth::id();

            //User
            $user = User::findOrFail($userID);

            //Interview
            $interviewID = Crypt::decryptString($request->id);

            // Fetch the interview based on the user's role:
            $interview = ($user->role_id <= 6)
            ? Interview::where('id', $interviewID)->where('interviewer_id', $userID)->firstOrFail()
            : Interview::where('id', $interviewID)->where('applicant_id', $user->applicant_id)->firstOrFail();

            // Merge decrypted IDs back into the request for validation purposes
            $request->merge([
                'interview_id' => $interview->id,
            ]);

            // Validate that the request has an interview_id
            $request->validate([
                'interview_id' => 'required|exists:interviews,id',
                'reschedule_time' => 'required'
            ]);

            // Check if the interview status is one of 'Scheduled', 'Confirmed', or 'Reschedule'
            if (!in_array($interview->status, ['Scheduled', 'Confirmed', 'Reschedule'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interview can only be rescheduled if it is in Scheduled, Confirmed, or Reschedule status.',
                ], 400);
            }

            // Attempt to parse the reschedule_time
            try {
                // Parse the date
                $rescheduleDateTime = Carbon::createFromFormat('d M, Y H:i', $request->reschedule_time);
            } catch (\Exception $e) {
                // If the parsing fails, return an error
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date time format!',
                ], 422);
            }

            // Check if the rescheduled date is in the future
            if ($rescheduleDateTime->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The rescheduled date must be in the future.',
                ], 400);
            }

            // Check if the rescheduled date is after the original scheduled date
            if ($rescheduleDateTime->lt($interview->scheduled_date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The rescheduled date must be after the scheduled date.',
                ], 400);
            }

            // Determine the reschedule update based on the user's role
            $rescheduleBy = ($user->role_id <= 6) ? 'Manager' : 'Applicant';

            // Update the interview status to 'Reschedule' with the new reschedule date and reschedule_by value
            $interview->update([
                'status' => 'Reschedule',
                'reschedule_date' => $rescheduleDateTime,
                'reschedule_by' => $rescheduleBy
            ]);

            //Applicant
            $applicant = Applicant::find($interview->applicant_id);

            // Create a notification based on the user's role
            if ($user->role_id <= 6 && $applicant) {
                // If a new interview was updated, then create a notification
                if ($interview->wasChanged() && $applicant->user) {
                    // Create Notification
                    $notification = new Notification();
                    $notification->user_id = $interview->applicant && $interview->applicant->user ? $interview->applicant->user->id : null;
                    $notification->causer_id = $userID;
                    $notification->subject()->associate($interview);
                    $notification->type_id = 1;
                    $notification->notification = "Requested to reschedule 📅";
                    $notification->read = "No";
                    $notification->save();
                }

                // Get the state_id for 'scheduled_start'
                $scheduledStateId = State::where('code', 'schedule_start')->value('id');

                // If scheduled state exists then update applicant
                if ($scheduledStateId) {
                    // Update the applicant's state
                    $applicant->update(['state_id' => $scheduledStateId]);
                }

                // Prepare a WhatsApp message
                $whatsappMessage = "You have a request to reschedule your interview to " .
                    $interview->reschedule_date->format('d M Y \a\t H:i') . 
                    ". Would you like to view the details?";

                // Define the message type
                $type = 'template';

                // Define the template for a confirmed interview
                $template = 'interview_reschedule_view';

                // Prepare the variables for the WhatsApp template
                $variables = [
                    $interview->applicant->firstname ?: 'N/A',  // Applicant's first name
                    $interview->reschedule_date->format('d M Y') ?: 'N/A', // Rescheduled date (only the date)
                    $interview->reschedule_date->format('H:i') ?: 'N/A' // Rescheduled time (only the time)
                ];

                // Dispatch WhatsApp message
                SendWhatsAppMessage::dispatch($interview->applicant, $whatsappMessage, $type, $template, $variables);
            } else {
                // If a new interview was updated, then create a notification
                if ($interview->wasChanged()) {
                    // Create Notification
                    $notification = new Notification();
                    $notification->user_id = $interview->interviewer_id;
                    $notification->causer_id = $userID;
                    $notification->subject()->associate($interview);
                    $notification->type_id = 1;
                    $notification->notification = "Requested to reschedule 📅";
                    $notification->read = "No";
                    $notification->save();
                }
            }

            // Encrypted interview ID
            $encryptedID = Crypt::encryptString($interviewID);

            // Return a success response
            return response()->json([
                'success' => true,
                'interview' => $interview,
                'encryptedID' => $encryptedID,
                'message' => 'Request for reschedule successful!',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation failure
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Return a error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to reschedule interview.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Complete
    |--------------------------------------------------------------------------
    */

    public function complete(Request $request)
    {
        try {
            // User ID
            $userID = Auth::id();

            //Interview
            $interviewID = Crypt::decryptString($request->id);
            $interview = Interview::where('id', $interviewID)
                                  ->where('interviewer_id', $userID)
                                  ->firstOrFail();

            // Merge decrypted IDs back into the request for validation purposes
            $request->merge([
                'interview_id' => $interview->id,
            ]);

            // Validate that the request has an interview_id
            $request->validate([
                'interview_id' => 'required|exists:interviews,id'
            ]);

            // Check if the interview status is one of 'Scheduled', 'Confirmed', or 'Reschedule'
            if (!in_array($interview->status, ['Scheduled', 'Confirmed', 'Reschedule'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interview can only be completed if it is in Scheduled, Confirmed, or Reschedule status.',
                ], 400);
            }

            //Applicant
            $applicant = Applicant::findOrFail($interview->applicant_id);

            //Vacancy
            $vacancy = Vacancy::findOrFail($interview->vacancy_id);

            // Vacancy ID
            $vacancyId = $vacancy->id;

            //Interview Update
            $interview->update([
                'status' => 'Completed'
            ]);

            // If a new interview was updated, then create a notification
            if ($applicant->user && $interview->wasChanged()) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $interview->applicant->user->id;
                $notification->causer_id = $userID;
                $notification->subject()->associate($interview);
                $notification->type_id = 1;
                $notification->notification = "Completed your interview 🚀";
                $notification->read = "No";
                $notification->save();
            }

            //Update Applicant Monthly Data
            UpdateApplicantData::dispatch($interview->applicant->id, 'updated', 'Interviewed', $vacancyId)->onQueue('default');

            return response()->json([
                'success' => true,
                'interview' => $interview,
                'message' => 'Interview cancelled!',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation failure
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel interview.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(Request $request)
    {
        try {
            // User ID
            $userID = Auth::id();

            //Interview
            $interviewID = Crypt::decryptString($request->id);
            $interview = Interview::where('id', $interviewID)
                                  ->where('interviewer_id', $userID)
                                  ->firstOrFail();

            // Check if the interview exists
            if (!$interview) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interview not found or unauthorized access.',
                ], 404);
            }


            // Merge decrypted IDs back into the request for validation purposes
            $request->merge([
                'interview_id' => $interview->id,
            ]);

            // Validate that the request has an interview_id
            $request->validate([
                'interview_id' => 'required|exists:interviews,id'
            ]);

            // Check if the interview status is one of 'Scheduled', 'Confirmed', or 'Reschedule'
            if (!in_array($interview->status, ['Scheduled', 'Confirmed', 'Reschedule'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interview can only be canceled if it is in Scheduled, Confirmed, or Reschedule status.',
                ], 400);
            }

            //Applicant
            $applicant = Applicant::findOrFail($interview->applicant_id);

            //Vacancy
            $vacancy = Vacancy::findOrFail($interview->vacancy_id);

            //Interview Update
            $interview->update([
                'status' => 'Cancelled'
            ]);

            // If a new interview was updated, then create a notification
            if ($applicant->user && $interview->wasChanged()) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $interview->applicant->user->id;
                $notification->causer_id = $userID;
                $notification->subject()->associate($interview);
                $notification->type_id = 1;
                $notification->notification = "Interview Cancelled 📅";
                $notification->read = "No";
                $notification->save();
            }

            // Prepare a congratulatory WhatsApp message for the applicant
            $whatsappMessage = "Dear " . $applicant->firstname ?: 'N/A' . ", we regret to inform you that your scheduled interview for the " .
                optional($vacancy->position)->name ?: 'N/A' . " position at " .
                optional($vacancy->store->brand)->name ?: 'N/A' . " (" .
                optional($vacancy->store->town)->name ?: 'N/A' . "), which was set for " .
                optional($interview->scheduled_date)->format('d M Y') . " at " .
                optional($interview->scheduled_time)->format('H:i') . ", has been canceled.
                We apologize for any inconvenience this may cause and appreciate your understanding. If there are any changes or future opportunities, 
                we will be sure to reach out.";

                // Define the message type
                $type = 'template';

                // Define the template
                $template = 'interview_cancel';

                // Prepare the variables (you can define these as per your needs)
                $variables = [
                    $applicant->firstname ?: 'N/A',  // If $applicant->firstname is null, use 'N/A'
                    optional($vacancy->position)->name ?: 'N/A',  // If $vacancy->position or its name is null, use 'N/A'
                    optional($vacancy->store->brand)->name ?: 'N/A',  // If $vacancy->store->brand or its name is null, use 'N/A'
                    optional($vacancy->store->town)->name ?: 'N/A',  // If $vacancy->store->town or its name is null, use 'N/A'
                    optional($interview->scheduled_date)->format('d M Y') ?: 'N/A', // Interview date
                    optional($interview->start_time)->format('H:i') ?: 'N/A' // Interview start time
                ];                  

                // Dispatch a job to send the WhatsApp message
                SendWhatsAppMessage::dispatch($applicant, $whatsappMessage, $type, $template, $variables);

            return response()->json([
                'success' => true,
                'interview' => $interview,
                'message' => 'Interview cancelled!',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation failure
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel interview.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview No Show
    |--------------------------------------------------------------------------
    */

    public function noShow(Request $request)
    {
        try {
            // User ID
            $userID = Auth::id();

            //Interview
            $interviewID = Crypt::decryptString($request->id);
            $interview = Interview::where('id', $interviewID)
                                  ->where('interviewer_id', $userID)
                                  ->firstOrFail();

            // Check if the interview exists
            if (!$interview) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interview not found or unauthorized access.',
                ], 404);
            }

            // Merge decrypted IDs back into the request for validation purposes
            $request->merge([
                'interview_id' => $interview->id,
            ]);

            // Validate that the request has an interview_id
            $request->validate([
                'interview_id' => 'required|exists:interviews,id'
            ]);

            // Check if the interview status is one of 'Scheduled', 'Confirmed', or 'Reschedule'
            if (!in_array($interview->status, ['Scheduled', 'Confirmed', 'Reschedule'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interview can only be marked as No Show if it is in Scheduled, Confirmed, or Reschedule status.',
                ], 400);
            }

            //Applicant
            $applicant = Applicant::findOrFail($interview->applicant_id);

            //Vacancy
            $vacancy = Vacancy::findOrFail($interview->vacancy_id);

            // Update the interview status to "No Show"
            $interview->update([
                'status' => 'No Show'
            ]);

            if ($applicant) {
                $applicant->increment('no_show');

                // Check if the 'no_show' count is >= 2
                if ($applicant->no_show >= 2 && $interview->wasChanged()) {
                    // Create Notification for No Show
                    $notification = new Notification();
                    $notification->user_id = $applicant->id;
                    $notification->causer_id = $userID;
                    $notification->subject()->associate($interview); // Associate notification with the interview or application
                    $notification->type_id = 1;
                    $notification->notification = "Has been declined 🚫";
                    $notification->read = "No";
                    $notification->save();
                }
            }

            return response()->json([
                'success' => true,
                'interview' => $interview,
                'message' => 'Interview marked as No Show!',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation failure
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark interview as No Show.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Score
    |--------------------------------------------------------------------------
    */

    public function score(Request $request)
    {
        $request->validate([
            'interview_id' => 'required',
            'answers' => 'required|array',
            'answers.*' => 'required|integer|min:1|max:5',
        ]);

        try {
            // User ID
            $userID = Auth::id();

            // Decrypt and find the interview by ID
            $interviewID = Crypt::decryptString($request->input('interview_id'));
            $interview = Interview::findOrFail($interviewID);

            // Vacancy ID
            $vacancyId = $interview->vacancy_id;

            // Initialize score sum
            $scoreSum = 0;

            // Update the interview with the scores
            foreach ($request->input('answers') as $questionId => $rating) {
                // Save each rating per question
                $scoreSum += $rating;
            }

            // Calculate the average score
            $averageScore = $scoreSum / count($request->input('answers'));
            $averageScore = round($averageScore, 2); // Round to 2 decimal places

            // Save the average score to the interview
            $interview->score = $averageScore;
            $interview->status = 'Completed';
            $interview->save();

            //User
            $user = User::where('applicant_id', $interview->applicant_id)->first();

            // If a new interview was updated, then create a notification
            if ($user && $interview->wasChanged()) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $user->id;
                $notification->causer_id = $userID;
                $notification->subject()->associate($interview);
                $notification->type_id = 1;
                $notification->notification = "Completed your interview 🚀";
                $notification->read = "No";
                $notification->save();
            }

            //Update Applicant Monthly Data
            UpdateApplicantData::dispatch($interview->applicant->id, 'updated', 'Interviewed', $vacancyId)->onQueue('default');

            return response()->json([
                'success' => true,
                'message' => 'Interview score submitted!',
                'score' => $averageScore
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit interview score.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Send Contract
    |--------------------------------------------------------------------------
    */

    public function contract(Request $request)
    {
        try {
            $applicants = $request->input('applicants_contracts');
            $contractFile = $request->file('contract_file');

            // Store the file and get the path
            $filePath = $contractFile->store('public/contracts');

            // Get the URL of the stored file
            $fileUrl = url('storage/contracts/' . basename($filePath));

            foreach ($applicants as $applicantId) {
                // Retrieve applicant details here, e.g., phone number
                $applicant = Applicant::find($applicantId);

                // Dispatch job to send WhatsApp message
                SendWhatsAppFile::dispatch($applicant, $fileUrl);

                // Create and save the contract record
                $contract = new Contract([
                    'applicant_id' => $applicantId,
                    'contract' => $fileUrl
                ]);
                $contract->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Contract sent succesfully!',
            ]);
        } catch (\Exception $e) {
            // Return an error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to send contract.',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
