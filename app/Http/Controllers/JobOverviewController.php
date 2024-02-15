<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\File;
use App\Models\Vacancy;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class JobOverviewController extends Controller
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
    | Opportunity Overview Index
    |--------------------------------------------------------------------------
    */

    public function index($id)
    {
        if (view()->exists('job-overview')) {
            //User ID
            $userID = Auth::id();

            //User
            $user = User::with([
                'appliedVacancies',
                'savedVacancies'
            ])
            ->findOrFail($userID);

            //Vacancy ID
            $vacancyID = Crypt::decryptString($id);

            //Vacancy
            $vacancy = Vacancy::with([
                'user', 
                'position.responsibilities',
                'position.qualifications',
                'position.skills',
                'position.experienceRequirements',
                'position.physicalRequirements',
                'position.workingHours',
                'position.salaryBenefits',
                'position.successFactors',
                'position.files',
                'store.brand',
                'store.town',
                'type',
                'status',
                'appointed',
                'applicants' => function ($query) use ($vacancyID) {
                    $query->where('vacancy_id', $vacancyID);
                },
                'savedBy' => function ($query) use ($userID) {
                    $query->where('user_id', $userID);
                },
            ])->findOrFail($vacancyID);

            //Check if user is applied
            $userApplied = $vacancy->applicants->contains(function ($user) use ($userID) {
                return $user->id === $userID && $user->pivot->approved === 'Yes';
            });

            //User application approval pending
            $userPendingApproval = $vacancy->applicants->contains(function ($user) use ($userID) {
                return $user->id === $userID && $user->pivot->approved === 'Pending';
            });

            //User application declined approval pending
            $userDeclined = $vacancy->applicants->contains(function ($user) use ($userID) {
                return $user->id === $userID && $user->pivot->approved === 'No';
            });

            //Vacancies
            $vacancies = Vacancy::with([
                'user',
                'position.tags',
                'store.brand',
                'store.town',
                'type',
                'status',
                'applicants' => function ($query) use ($vacancyID) {
                    $query->where('vacancy_id', $vacancyID);
                },
                'savedBy' => function ($query) use ($userID) {
                    $query->where('user_id', $userID);
                }
            ])
            ->where('id', '!=', $vacancyID)
            ->where('status_id', 2)
            ->orderBy('created_at', 'desc')
            ->get();

            //Share URL
            $vacancyUrl = url('/job-overview/' . Crypt::encryptString($vacancy->id));
            $textToShare = "Check out this job opportunity: " . $vacancy->position->name . " - " . Str::limit($vacancy->position->description, 100);
            $encodedTextToShare = urlencode($textToShare);

            // URLs for sharing the vacancy on different platforms
            $facebookShareUrl = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($vacancyUrl) . "&quote=" . $encodedTextToShare;
            $whatsappShareUrl = "https://api.whatsapp.com/send?text=" . $encodedTextToShare . " " . urlencode($vacancyUrl);
            $twitterShareUrl = "https://twitter.com/intent/tweet?text=" . $encodedTextToShare . "&url=" . urlencode($vacancyUrl);
            $mailShareUrl = "mailto:?subject=" . $encodedTextToShare . "&body=Check out this job opportunity here: " . urlencode($vacancyUrl);

            return view('job-overview',[
                'user' => $user,
                'vacancy' => $vacancy,
                'userApplied' => $userApplied,
                'userPendingApproval' => $userPendingApproval,
                'userDeclined' => $userDeclined,
                'vacancies' => $vacancies,
                'vacancyUrl' => $vacancyUrl,
                'facebookShareUrl' => $facebookShareUrl,
                'whatsappShareUrl' => $whatsappShareUrl,
                'twitterShareUrl' => $twitterShareUrl,
                'mailShareUrl' => $mailShareUrl,
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | File View
    |--------------------------------------------------------------------------
    */

    public function viewFile($id)
    {
        //File ID
        $id = Crypt::decryptString($id);

        //File
        $file = File::findOrFail($id);

        //Path
        $path = storage_path('app/public/positions/' . $file->name);

        if ($file->type == 'csv') {
            return response()->download($path, $file->original_name);
        } else {
            return response()->file($path);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | File Download
    |--------------------------------------------------------------------------
    */

    public function downloadFile($id)
    {
        //File ID
        $id = Crypt::decryptString($id);

        //File
        $file = File::findOrFail($id);

        //Path
        $path = storage_path('app/public/positions/' . $file->name);
        
        return response()->download($path, $file->original_name);
    }

    /*
    |--------------------------------------------------------------------------
    | File Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        try {
            $positionID = Crypt::decryptString($request->position_id);

            // Handle the file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filetype = $file->getClientOriginalExtension();
                $filesize = $file->getSize() / (1024 * 1024); // size in MB

                // Construct the filename with timestamp
                $filename = $file->getClientOriginalName() . '-' . time() . '.' . $filetype;

                // Save the file to storage/app/public/positions
                $path = $file->storeAs('positions', $filename, 'public');
                $fullPath = '/app/public/' . $path;

                // Create a new record in the database
                $fileRecord = File::create([
                    'position_id' => $positionID,
                    'name' => $filename,
                    'type' => $filetype,
                    'size' => $filesize,
                    'url' => $fullPath
                ]);

                return response()->json([
                    'success' => true,                    
                    'file' => $fileRecord,
                    'encrypted_id' => Crypt::encryptString($fileRecord->id),
                    'upload_date' => $fileRecord->created_at->format('d M Y'),
                    'message' => 'File uploaded successfully!',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File could not be uploaded!'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | File Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            //Delete File
            File::destroy($id);

            return response()->json([
                'success' => true, 
                'message' => 'File deleted!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'File deletion failed', 
                'error' => $e->getMessage()
            ], 400);
        }
    }
}