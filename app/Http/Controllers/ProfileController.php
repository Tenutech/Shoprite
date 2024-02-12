<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Vacancy;
use App\Models\Position;
use App\Models\Document;
use App\Models\Applicant;
use App\Models\Application;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Spatie\Activitylog\Models\Activity;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except('root');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /*
    |--------------------------------------------------------------------------
    | Profile Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        if (view()->exists('profile')) {
            //User ID
            $userID = Auth::id();

            //User
            $user = User::with([
                'role', 
                'status', 
                'company', 
                'position', 
                'vacancies',
                'applicant.town',
                'applicant.gender',
                'applicant.race',
                'applicant.position',
                'applicant.education',
                'applicant.readLanguages',
                'applicant.speakLanguages',
                'applicant.reason',
                'applicant.duration',
                'applicant.retrenchment',
                'applicant.brand',
                'applicant.previousPosition',
                'applicant.transport',
                'applicant.disability',
                'applicant.type',
                'applicant.bank',
                'applicant.role',
                'applicant.state',
                'appliedVacancies', 
                'savedVacancies', 
                'files', 
                'messagesFrom',
                'messagesTo',
                'notifications'
            ])
            ->findorfail($userID);

            //Completed
            $fields = [
                'firstname',
                'lastname',
                'email',
                'phone',
                'avatar',
                'company_id',
                'position_id',
                'website'
            ];
            
            //Completion Percentage
            $completion = 0;
            if ($user->applicant) {
                $completion = round(($user->applicant->state_id/69)*100);
                if ($completion > 100) {
                    $completion = 100;
                }
            }

            //Activity Log

            // Define the models that are relevant for the activity log.
            $allowedModels = [
                'App\Models\Applicant', 
                'App\Models\Application', 
                'App\Models\Vacancy', 
                'App\Models\Message', 
                'App\Models\User'
            ];

            // Retrieve the ID of the currently authenticated user.
            $authUserId = Auth::id();
            // Get a list of IDs for vacancies that are associated with the authenticated user.
            $authVacancyIds = Vacancy::where('user_id', $authUserId)->pluck('id')->toArray();

            // Query the activity log, filtering for activities related to the allowed models.
            $activities = Activity::whereIn('subject_type', $allowedModels)
                ->where(function($query) use ($authUserId, $authVacancyIds) {
                    // Filter for activities where the 'causer' (the user who performed the action) is the authenticated user,
                    // and the action is one of 'created', 'updated', or 'deleted'.
                    $query->where('causer_id', $authUserId)
                        ->whereIn('event', ['created', 'updated', 'deleted']);
                })
                ->orWhere(function($q) use ($authUserId) {
                    // Include activities where the event is 'accessed' (e.g., a user viewed a vacancy or applicant profile),
                    // specifically for the authenticated user.
                    $q->where('event', 'accessed')
                    ->whereIn('description', ['job-overview.index', 'applicant-profile.index'])
                    ->where('causer_id', $authUserId);
                })
                ->orWhere(function($q) use ($authUserId) {
                    // Include activities related to messages where the authenticated user is the recipient ('to_id').
                    $q->where('subject_type', 'App\Models\Message')
                    ->where('properties->attributes->to_id', $authUserId)
                    ->where('event', 'created');
                })
                ->orWhere(function($q) use ($authVacancyIds) {
                    // Include activities related to applications connected to any of the vacancies owned by the authenticated user.
                    $q->where('subject_type', 'App\Models\Application')
                    ->whereIn('properties->attributes->vacancy_id', $authVacancyIds);
                })
                ->latest() // Order the results by the most recent first.
                ->get(); // Execute the query and get the results

            // Filter activities to get only those related to vacancies.
            $vacancyActivities = $activities->where('subject_type', 'App\Models\Vacancy');
            // Extract the IDs of the affected vacancies from these activities.
            $vacancyIds = $vacancyActivities->pluck('subject_id');

            // Retrieve the vacancies along with their related models like position, store's brand, store's town, and type.
            $vacanciesWithRelations = Vacancy::with(['position', 'store.brand', 'store.town', 'type'])
                                            ->whereIn('id', $vacancyIds)
                                            ->get();

            // Associate each activity with its corresponding vacancy by setting a relation.
            foreach ($vacancyActivities as $activity) {
                $activity->setRelation('subject', $vacanciesWithRelations->firstWhere('id', $activity->subject_id));
            }

            // Filter activities to get only those related to messages.
            $messageActivities = $activities->where('subject_type', 'App\Models\Message');
            // Extract the IDs of the messages from these activities.
            $ids = $messageActivities->pluck('subject_id');

            // Retrieve the messages along with their related 'to' and 'from' users.
            $messagesWithRelations = Message::with('to', 'from')->whereIn('id', $ids)->get();

            // Associate each message activity with its corresponding message by setting a relation.
            foreach ($messageActivities as $activity) {
                $activity->setRelation('subject', $messagesWithRelations->firstWhere('id', $activity->subject_id));
            }

            // Filter activities to get only those related to applications.
            $applicationActivities = $activities->where('subject_type', 'App\Models\Application');
            // Extract the IDs of the applications from these activities.
            $connectionIds = $applicationActivities->pluck('subject_id');

            // Retrieve the applications along with their related user and the user of the related vacancy.
            $applicationWithRelations = Application::with(['user', 'vacancy.user'])->whereIn('id', $connectionIds)->get();

            // Associate each application activity with its corresponding application by setting a relation.
            foreach ($applicationActivities as $activity) {
                $activity->setRelation('subject', $applicationWithRelations->firstWhere('id', $activity->subject_id));
            }

            // For activities where messages have been deleted, extract the 'to_id' from the old properties.
            $deletedMessageUserIds = $activities->where('subject_type', 'App\Models\Message')
                                                ->where('event', 'deleted')
                                                ->map(function ($activity) {
                                                    return data_get($activity, 'properties.old.to_id');
                                                })
                                                ->filter()
                                                ->unique();

            // Retrieve the users associated with the deleted messages.
            $usersForDeletedMessages = User::whereIn('id', $deletedMessageUserIds)->get();

            // Filter activities to get only those where messages have been deleted.
            $deletedMessageActivities = $activities->where('subject_type', 'App\Models\Message')->where('event', 'deleted');

            // Associate each deleted message activity with the user it was sent to by setting a relation.
            foreach ($deletedMessageActivities as $activity) {
                $toId = data_get($activity, 'properties.old.to_id');
                $activity->setRelation('userForDeletedMessage', $usersForDeletedMessages->firstWhere('id', $toId));
            }           

            // Extract the encrypted IDs from the URL of the 'accessed' activities for vacancies.
            $accessedVacancyEncryptedIds = $activities->where('event', 'accessed')
            ->where('description', 'job-overview.index')
            ->map(function ($activity) {
                // Get the URL from the activity's properties.
                return data_get($activity, 'properties.url');
            })
            ->map(function($url) {
                // Split the URL into segments and get the last segment, which is the encrypted ID.
                $segments = explode('/', $url);
                $encryptedId = count($segments) > 1 ? last($segments) : null;

                // Attempt to decrypt the encrypted ID.
                if ($encryptedId) {
                    try {
                        return Crypt::decryptString($encryptedId);
                    } catch (\Exception $e) {
                        // If decryption fails, return null.
                        return null;
                    }
                }
                return null;
            })
            ->filter(); // Remove any null values from the collection.

            // Retrieve the vacancies that have been accessed using the decrypted IDs.
            $accessedOpportunities = Vacancy::whereIn('id', $accessedVacancyEncryptedIds)->get();

            // Filter the activities to get only those with 'accessed' event and 'job-overview.index' description.
            $accessedVacancyActivities = $activities->where('event', 'accessed')->where('description', 'job-overview.index');

            // Associate each accessed vacancy activity with the corresponding vacancy.
            foreach ($accessedVacancyActivities as $activity) {
                // Decrypt the encrypted ID from the URL.
                $encryptedId = last(explode('/', data_get($activity, 'properties.url')));
                try {
                    $decryptedId = Crypt::decryptString($encryptedId);
                    // Find the vacancy using the decrypted ID and set the relation.
                    $opportunity = $accessedOpportunities->firstWhere('id', $decryptedId);
                    $activity->setRelation('accessedVacancy', $opportunity);
                } catch (\Exception $e) {
                    // If decryption fails, skip this iteration.
                    continue;
                }
            }

            // Extract the encrypted IDs from the URL of the 'accessed' activities for applicants.
            $accessedApplicantEncryptedIds = $activities->where('event', 'accessed')
            ->where('description', 'applicant-profile.index')
            ->map(function ($activity) {
                // Parse the URL to get the query string, then extract the 'id' parameter.
                parse_str(parse_url(data_get($activity, 'properties.url'), PHP_URL_QUERY), $queryParams);
                return $queryParams['id'] ?? null;
            })
            ->map(function($encryptedId) {
                // Attempt to decrypt the encrypted ID.
                if ($encryptedId) {
                    try {
                        return Crypt::decryptString($encryptedId);
                    } catch (\Exception $e) {
                        // If decryption fails, return null.
                        return null;
                    }
                }
                return null;
            })
            ->filter(); // Remove any null values from the collection.

            // Retrieve the applicants that have been accessed using the decrypted IDs.
            $accessedApplicants = Applicant::whereIn('id', $accessedApplicantEncryptedIds)->get();

            // Filter the activities to get only those with 'accessed' event and 'applicant-profile.index' description.
            $accessedApplicantActivities = $activities->where('event', 'accessed')->where('description', 'applicant-profile.index');

            // Associate each accessed applicant activity with the corresponding applicant.
            foreach ($accessedApplicantActivities as $activity) {
                // Parse the URL to get the encrypted ID from the query string.
                parse_str(parse_url(data_get($activity, 'properties.url'), PHP_URL_QUERY), $queryParams);
                $encryptedId = $queryParams['id'] ?? null;
                if ($encryptedId) {
                    try {
                        // Decrypt the encrypted ID and find the corresponding applicant.
                        $decryptedId = Crypt::decryptString($encryptedId);
                        $applicant = $accessedApplicants->firstWhere('id', $decryptedId);
                        // Set the relation on the activity.
                        $activity->setRelation('accessedApplicant', $applicant);
                    } catch (\Exception $e) {
                        // If decryption fails or the applicant is not found, skip this iteration.
                        continue;
                    }
                }
            }

            //Tabs
            $tabs = [
                'today' => [
                    'label' => 'Today',
                    'start' => now()->startOfDay(),
                    'end' => now(),
                    'active' => true
                ],
                'weekly' => [
                    'label' => 'This Week',
                    'start' => now()->subDays(7)->startOfDay(),
                    'end' => now()->startOfDay(),
                    'active' => false
                ],
                'monthly' => [
                    'label' => 'Older',
                    'start' => null, // No start for "older" as it's everything before the week
                    'end' => now()->subDays(7)->startOfDay(),
                    'active' => false
                ],
            ];

            //Top Vacancies
            $topVacancies = Vacancy::with([
                'position', 
                'store.brand',
                'store.town',
                'type',
                'applicants'
            ])
            ->withCount('applicants')
            ->where('status_id', 2)               
            ->orderBy('applicants_count', 'desc')
            ->take(3)
            ->get();

            return view('profile',[
                'user' => $user,
                'completion' => $completion,
                'activities' => $activities,
                'tabs' => $tabs,
                'topVacancies' => $topVacancies,
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
        //UserID
        $userID = Auth::id();

        //File ID
        $fileID = Crypt::decryptString($id);

        //File
        $file = Document::findOrFail($fileID);

        //Path
        $path = storage_path('app/public/users/' . $userID . '/' . $file->name);

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
        //UserID
        $userID = Auth::id();

        //File ID
        $fileID = Crypt::decryptString($id);

        //File
        $file = Document::findOrFail($fileID);

        //Path
        $path = storage_path('app/public/users/' . $userID . '/' . $file->name);
        
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
            //UserID
            $userID = Auth::id();

            // Handle the file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filetype = $file->getClientOriginalExtension();
                $filesize = $file->getSize() / (1024 * 1024); // size in MB

                // Construct the filename with timestamp
                $filename = $file->getClientOriginalName() . '-' . time() . '.' . $filetype;

                // Define the path where the file will be stored
                $storagePath = "users/{$userID}";

                // Save the file to storage/app/public/users/$userID
                $path = $file->storeAs($storagePath, $filename, 'public');
                $fullPath = "/storage/{$path}";

                // Create a new record in the database
                $fileRecord = Document::create([
                    'user_id' => $userID,
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
            // Retrieve the file from the database
            $file = Document::findOrFail($id);

            // Construct the file path
            $filePath = storage_path('app/public/users/' . $file->user_id . '/' . $file->name);

            // Check if the file exists and delete it from the storage
            if (file_exists($filePath)) {
                \File::delete($filePath);
            }

            // Delete the file record from the database
            $file->delete();

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

    /*
    |--------------------------------------------------------------------------
    | Profile Delete
    |--------------------------------------------------------------------------
    */

    public function deleteProfile(Request $request)
    {
        // Add logic to delete the user's profile here

        auth()->logout(); // Logout the user

        return response()->json(['success' => true, 'redirect' => url('/')]);
    }
}