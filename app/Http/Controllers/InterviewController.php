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

    /*
    |--------------------------------------------------------------------------
    | Interview Create
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
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
            'applicants' => 'required|array',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Get the state_id for 'scheduled'
        $scheduledStateId = State::where('code', 'schedule')->value('id');

        $messages = ChatTemplate::where('state_id', $scheduledStateId)
                                ->orderBy('sort')
                                ->get();

        try {
            // Start a transaction
            DB::beginTransaction();            

            // Loop through each applicant and create an interview
            foreach ($validatedData['applicants'] as $applicantID) {
                $this->scheduleInterviewForApplicant($applicantID, $validatedData, $scheduledStateId);
                //$this->sendWhatsAppMessages($applicantID, $messages);


            }

            // Commit the transaction
            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Interviews scheduled successfully.',
                'date' => $interviewDate->format('d M'),
                'time' => $startTime->format('H:i')
            ]);
        } catch (\Exception $e) {
            // An error occurred; cancel the transaction
            DB::rollBack();

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

        $latestInterview = $applicant->interviews->sortByDesc('scheduled_date')->first();

        $dataToReplace = [
            "Applicant Name" => $applicant->firstname.' '.$applicant->lastname,
            "Position Name" => $latestInterview->vacancy->position->name ?? 'N/A',
            "Store Name" => ($latestInterview->vacancy->store->brand->name ?? '') . ' ' . ($latestInterview->vacancy->store->town->name ?? 'Our Office'),
            "Interview Location" => $latestInterview->location ?? 'N/A',
            "Interview Date" => $latestInterview->scheduled_date->format('d M Y'),
            "Interview Time" => $latestInterview->start_time->format('H:i'),
            "Notes" => $latestInterview->notes ?? 'None provided',
        ];
    

        foreach ($messages as $message) {
            $personalizedMessage = $this->replacePlaceholders($message->message, $dataToReplace);
    
            // Dispatch the job to send WhatsApp messages
            SendWhatsAppMessage::dispatch($applicant, $personalizedMessage);
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

            //Interview
            $interviewID = Crypt::decryptString($request->id);
            $interview = Interview::findOrFail($interviewID);
            
            //Application Update
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

            $encryptedID = Crypt::encryptString($interviewID);
            
            return response()->json([
                'success' => true,
                'encryptedID' => $encryptedID,
                'message' => 'Interview confirmed!',
            ], 201);
        } catch (\Exception $e) {
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

            //Interview
            $interviewID = Crypt::decryptString($request->id);
            $interview = Interview::findOrFail($interviewID);
            
            //Application Update
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
                $notification->notification = "Declined your application request 🚫";
                $notification->read = "No";
                $notification->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Interview declined!',
            ], 201);
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
