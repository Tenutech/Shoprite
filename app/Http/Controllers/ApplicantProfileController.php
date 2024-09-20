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
                'user.appliedVacancies',
                'town',
                'gender',
                'race',
                'position',
                'education',
                'readLanguages',
                'speakLanguages',
                'reason',
                'duration',
                'retrenchment',
                'brand',
                'previousPosition',
                'transport',
                'disability',
                'type',
                'bank',
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

            $user = $applicant->user;

            $documents = collect();

            if ($user) {
                $documents = Document::where('user_id', $user->id)->get();
            }

            //Vacancy ID
            $vacancyId = optional($applicant->shortlist)->vacancy_id;

            // If vacancyId exists, reload the interviews relationship with the specific vacancy filter
            if ($vacancyId) {
                $applicant->load(['interviews' => function ($query) use ($vacancyId) {
                    $query->where('vacancy_id', $vacancyId) // Filter by vacancy_id
                        ->latest('scheduled_date') // Get the latest interview based on scheduled date
                        ->take(1); // Limit to one result
                }]);
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
                // Check for applied vacancies
                if ($applicant->user && $applicant->user->appliedVacancies->count() > 0) {
                    $progressBarWidth = max($progressBarWidth, 40); // Second step
                }

                // Check for shortlist
                if ($applicant->shortlist_id) {
                    $progressBarWidth = max($progressBarWidth, 60); // Third step
                }

                // Check for interviews
                if ($applicant->interviews && $applicant->interviews->count() > 0) {
                    $progressBarWidth = max($progressBarWidth, 80); // Fourth step

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
                'vacancyId' => $vacancyId
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

        // Validate the request data
        $validatedData = $request->validate([
            'vacancy_id' => 'required|int',
            'applicant_id' => 'required|int',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Get the state_id for 'scheduled'
        $scheduledStateId = State::where('code', 'schedule_start')->value('id');

        $messages = ChatTemplate::where('state_id', $scheduledStateId)
                                ->orderBy('sort')
                                ->get();

        try {
            // Start a transaction
            DB::beginTransaction();

            $applicantID = $validatedData['applicant_id'];

            // Create an interview for applicant
            if ($applicantID) {
                $interview = $this->scheduleInterviewForApplicant($applicantID, $validatedData, $scheduledStateId);
                $this->sendWhatsAppMessages($applicantID, $messages);
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

            return response()->json([
                'success' => true,
                'message' => 'Interviews scheduled successfully.',
                'date' => $interviewDate->format('d M'),
                'time' => $startTime->format('H:i'),
                'questions' => $questions,
                'interviewId' => $interviewId
            ]);
        } catch (\Exception $e) {
            // An error occurred; cancel the transaction
            DB::rollBack();

            Log::error($e);

            // Return an error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule interviews.',
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
        //User ID
        $userID = Auth::id();

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
        ]);

        // Assuming your Applicant model has the state_id fillable or uses property accessors
        Applicant::where('id', $applicantID)->update(['state_id' => $scheduledStateId]);

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

         // Return the interview instance
        return $interview;
    }

    /*
    |--------------------------------------------------------------------------
    | Send Whatsapp
    |--------------------------------------------------------------------------
    */

    private function sendWhatsAppMessages($applicantID, $messages)
    {
        $applicant = Applicant::with([
            'interviews.vacancy.position',
            'interviews.vacancy.store.brand',
            'interviews.vacancy.store.town',
        ])->find($applicantID);

        // Reload the model to ensure all relationships are up to date.
        $applicant->load('interviews');

        $latestInterview = $applicant->interviews->sortByDesc('created_at')->first();

        $dataToReplace = [
            "Applicant Name" => $applicant->firstname . ' ' . $applicant->lastname,
            "Position Name" => $latestInterview->vacancy->position->name ?? 'N/A',
            "Store Name" => ($latestInterview->vacancy->store->brand->name ?? '') . ' ' . ($latestInterview->vacancy->store->town->name ?? 'Our Office'),
            "Interview Location" => $latestInterview->location ?? 'N/A',
            "Interview Date" => $latestInterview->scheduled_date->format('d M Y'),
            "Interview Time" => $latestInterview->start_time->format('H:i'),
            "Notes" => $latestInterview->notes ?? 'None provided',
        ];

        $type = 'template';

        foreach ($messages as $message) {
            $personalizedMessage = $this->replacePlaceholders($message->message, $dataToReplace);

            // Dispatch the job to send WhatsApp messages
            SendWhatsAppMessage::dispatch($applicant, $personalizedMessage, $type, $message->template);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Replace Message Placeholders
    |--------------------------------------------------------------------------
    */

    private function replacePlaceholders($template, $data)
    {
        foreach ($data as $key => $value) {
            $template = str_replace('[' . $key . ']', $value, $template);
        }

        return $template;
    }
}
