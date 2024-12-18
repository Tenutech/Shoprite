<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Chat;
use App\Models\State;
use App\Models\Vacancy;
use App\Models\Contract;
use App\Models\Document;
use App\Models\Applicant;
use App\Models\Shortlist;
use App\Models\Interview;
use App\Models\InterviewQuestion;
use App\Models\ChatTemplate;
use App\Models\Notification;
use App\Jobs\SendWhatsAppMessage;
use App\Jobs\SendWhatsAppFile;
use App\Jobs\UpdateApplicantData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Spatie\Activitylog\Models\Activity;

class ApplicantProfileController extends Controller
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
    | Applicant Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        if (view()->exists('manager/applicant-profile')) {
            //Applicant ID
            if ($request->id) {
                $applicantID = Crypt::decryptString($request->id);
            } else {
                $applicantID = null;
                return view('404');
            }

            //Auth User ID
            $authUserId = Auth::id();

            //User
            $authUser = User::findOrFail($authUserId);

            //Applicant
            $applicant = Applicant::with([
                'town',
                'gender',
                'race',
                'education',
                'duration',
                'brands',
                'role',
                'state',
                'chats.applicant',
                'chats.type',
                'checks',
                'latestChecks',
                'vacanciesFilled',
                'interviews' => function ($query) {
                    $query->latest('scheduled_date')->take(1); // Get the latest interview based on scheduled date
                },
            ])
            ->findorfail($applicantID);

            $documents = collect();

            if ($applicant) {
                $documents = Document::where('applicant_id', $applicant->id)->get();
            }

            //Vacancy ID
            $vacancyId = optional($applicant->shortlist)->vacancy_id;
            $vacancy = null;

            // If vacancyId exists, reload the interviews relationship with the specific vacancy filter
            if ($vacancyId) {
                $applicant->load(['interviews' => function ($query) use ($vacancyId) {
                    $query->where('vacancy_id', $vacancyId) // Filter by vacancy_id
                        ->latest('scheduled_date') // Get the latest interview based on scheduled date
                        ->take(1); // Limit to one result
                }]);

                $vacancy = Vacancy::find($vacancyId);
            }

            // Get the 'complete' state ID
            $completeStateID = State::where('code', 'complete')->value('id');

            //Completion Percentage
            $completion = round(($applicant->state_id / $completeStateID) * 100);
            if ($completion > 100) {
                $completion = 100;
            }

            // Get the latest interview
            $latestInterview = $applicant->interviews->first();

            // Initialize questions
            $questions = collect();

            if ($latestInterview) {
                // Get the vacancy's position's template_id
                $templateID = $latestInterview->vacancy->position->template_id;

                // Fetch interview questions for the template
                $questions = InterviewQuestion::where('template_id', $templateID)->get();
            }

            // Initialize progress bar width
            $progressBarWidth = 0;

            // Check each step in sequence, ensuring previous steps are completed
            if ($completion >= 100) {
                $progressBarWidth = 25;

                // Check for shortlist
                if ($applicant->shortlist_id) {
                    $progressBarWidth = max($progressBarWidth, 50); // Third step
                }

                // Check for interviews
                if ($applicant->interviews && $applicant->interviews->count() > 0) {
                    $progressBarWidth = max($progressBarWidth, 75); // Fourth step

                    // Check for interview score
                    $firstInterview = $applicant->interviews->first();
                    if ($firstInterview->score) {
                        $progressBarWidth = max($progressBarWidth, 100); // Sixth step
                    }
                }

                // Check for appointment
                if ($applicant->appointed_id) {
                    $progressBarWidth = 100; // Final step
                }
            }

            return view('manager/applicant-profile', [
                'authUser' => $authUser,
                'applicant' => $applicant,
                'documents' => $documents,
                'completion' => $completion,
                'questions' => $questions,
                'progressBarWidth' => $progressBarWidth,
                'vacancyId' => $vacancyId,
                'vacancy' => $vacancy
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Aplicant Messages
    |--------------------------------------------------------------------------
    */

    public function messages($id)
    {
        if ($id) {
            $applicantID = Crypt::decryptString($id);
        } else {
            $applicantID = null;
        }
        //Messages
        $messages = Chat::with([
            'applicant',
            'type',
        ])
        ->where('applicant_id', $applicantID)
        ->toArray();

        return response()->json($messages);
    }

    /*
    |--------------------------------------------------------------------------
    | Aplicant Check File
    |--------------------------------------------------------------------------
    */

    public function checkFile($filename)
    {
        $path = storage_path('app/checks/' . $filename);

        if (!Storage::exists('checks/' . $filename)) {
            abort(404);
        }

        return response()->file($path);
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Create
    |--------------------------------------------------------------------------
    */

    public function interview(Request $request)
    {
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
            'date' => $interviewDateFormatted,
            'start_time' => $startTimeFormatted,
            'end_time' => $endTimeFormatted,
        ]);

        // Get the current date and time
        $currentDateTime = Carbon::now();

        // Validate the date request data
        $validatedData = $request->validate([
            'vacancy_id' => 'required|int|exists:vacancies,id',
            'applicant_id' => 'required|int|exists:applicants,id',
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

        // Get the state_id for 'scheduled'
        $scheduledStateId = State::where('code', 'schedule_start')->value('id');

        try {
            // Start a transaction
            DB::beginTransaction();

            $applicantID = $validatedData['applicant_id'];

            // Create an interview for applicant
            if ($applicantID) {
                $interview = $this->scheduleInterviewForApplicant($applicantID, $validatedData, $scheduledStateId);
            }

            // Commit the transaction
            DB::commit();

            // Initialize questions
            $questions = collect();

            if ($interview) {
                // Get the vacancy's position's template_id
                $templateID = $interview->vacancy->position->template_id;

                // Fetch interview questions for the template
                $questions = InterviewQuestion::where('template_id', $templateID)->get();
            }

            //Encrypted Interview ID
            $interviewId = Crypt::encryptstring($interview->id);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Interview scheduled successfully!',
                'interview' => $interview,
                'date' => $interviewDate->format('d M'),
                'time' => $startTime->format('H:i'),
                'questions' => $questions,
                'interviewId' => $interviewId
            ]);
        } catch (\Exception $e) {
            // An error occurred; cancel the transaction
            DB::rollBack();

            // Return an error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule interview!',
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
            ->where('vacancy_id', $validatedData['vacancy_id'])
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
            ->where('vacancy_id', $validatedData['vacancy_id'])
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
            $template = 'interview_reschedule_view_2';

            // Prepare the variables for the WhatsApp template
            $variables = [
                $applicant->firstname ?: 'N/A',  // Applicant's first name
                Carbon::parse($existingInterview->reschedule_date)->format('d M Y') ?: 'N/A', // Rescheduled date
                Carbon::parse($existingInterview->reschedule_date)->format('H:i') ?: 'N/A', // Rescheduled time
            ];

            // Dispatch WhatsApp message
            SendWhatsAppMessage::dispatch($applicant, $whatsappMessage, $type, $template, $variables);

            // Return the interview instance
            return $existingInterview;
        } else {
            // Assuming your Interview model has the appropriate fillable attributes set
            $interview = Interview::create([
                'applicant_id' => $applicantID,
                'interviewer_id' => $userID,
                'vacancy_id' => $validatedData['vacancy_id'],
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
            $template = 'interview_send';

            // Prepare the variables for the WhatsApp template
            $variables = [
                $applicant->firstname ?: 'N/A'
            ];

            // Dispatch WhatsApp message
            SendWhatsAppMessage::dispatch($applicant, $message, $type, $template, $variables);

            // Return the interview instance
            return $interview;
        }
    }
}
