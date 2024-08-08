<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Chat;
use App\Models\Document;
use App\Models\Applicant;
use App\Models\Shortlist;
use App\Models\InterviewQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
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
            }

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

            //Completion Percentage
            $completion = round(($applicant->state_id/69)*100);
            if ($completion > 100) {
                $completion = 100;
            }

            //Top Applicants
            $topApplicants = Applicant::where('id', '!=', $applicantID)
            ->whereHas('state', function ($query) {
                $query->where('code', 'complete');
            })
            ->orderBy('score', 'desc')
            ->take(3)
            ->get();

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

            Log::info($progressBarWidth);

            return view('manager/applicant-profile',[
                'applicant' => $applicant,
                'documents' => $documents,
                'completion' => $completion,
                'topApplicants' => $topApplicants,
                'questions' => $questions,
                'progressBarWidth' => $progressBarWidth
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
        if ($request->id) {
            $applicantID = Crypt::decryptString($request->id);
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
}