<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Race;
use App\Models\Type;
use App\Models\User;
use App\Models\Brand;
use App\Models\Store;
use App\Models\Gender;
use App\Models\Reason;
use App\Models\Message;
use App\Models\Vacancy;
use App\Models\Language;
use App\Models\Position;
use App\Models\Duration;
use App\Models\Education;
use App\Models\Applicant;
use App\Models\Transport;
use App\Models\Disability;
use App\Models\Notification;
use App\Models\Retrenchment;
use App\Models\Application;
use App\Models\ChatTemplate;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Spatie\Activitylog\Models\Activity;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified'])->except(['root', 'policy', 'terms', 'security', 'subscribe']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /*
    |--------------------------------------------------------------------------
    | Pages Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Home Index
    |--------------------------------------------------------------------------
    */

    public function home()
    {
        if (view()->exists('home')) {
            //User ID
            $userID = Auth::id();

            //User
            $user = User::with([
                'applicant.readLanguages',
                'applicant.speakLanguages',
            ])->findOrFail($userID);

            //Vacancies
            $vacancies = Vacancy::with([
                'user',
                'position.tags',
                'store.brand',
                'store.town',
                'type',
                'status',
                'applicants',
                'savedBy' => function ($query) use ($userID) {
                    $query->where('user_id', $userID);
                }
            ])
            ->where('status_id', 2)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($vacancy) {
                $vacancy->encrypted_id = Crypt::encryptString($vacancy->id);
                $vacancy->encrypted_user_id = Crypt::encryptString($vacancy->user_id);
                return $vacancy;
            });

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
                ->limit(10) // Limit the results to the 10 most recent activities.
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

            //Positions
            $positions = Position::withCount('users')
                ->whereNotIn('id', [1, 10])
                ->orderBy('users_count', 'desc')
                ->take(10)
                ->get();

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

            return view('home',[
                'userID' => $userID,
                'user' => $user,
                'vacancies' => $vacancies,
                'activities' => $activities,
                'positions' => $positions,
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
    | Home Vacancies
    |--------------------------------------------------------------------------
    */

    public function vacancies()
    {
        //User ID
        $userID = Auth::id();

        //User
        $user = User::with('vacancies')->findOrFail($userID);

        //Vacancies
        $vacancies = Vacancy::with([
            'user',
            'position.tags',
            'store.brand',
            'store.town',
            'type',
            'status',
            'applicants',
            'savedBy' => function ($query) use ($userID) {
                $query->where('user_id', $userID);
            }
        ])
        ->where('status_id', 2)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($vacancy) {
            $vacancy->encrypted_id = Crypt::encryptString($vacancy->id);
            $vacancy->encrypted_user_id = Crypt::encryptString($vacancy->user_id);
            return $vacancy;
        })
        ->toArray();

        return response()->json([
            'userID' => $userID,
            'authUser' => $user,
            'vacancies' => $vacancies
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Root
    |--------------------------------------------------------------------------
    */

    public function root()
    {
        if (view()->exists('welcome')) {
            $positions = Position::get();

            return view('welcome', [
                'positions' => $positions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Translation
    |--------------------------------------------------------------------------
    */

    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Subscribe
    |--------------------------------------------------------------------------
    */

    public function subscribe(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:subscriptions,email', // assuming your table name is 'subscribes'
        ]);

        try {
            Subscription::create(['email' => $request->email]);

            return response()->json([
                'success' => true,
                'message' => 'Subscribed Successfully!'
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed To Subscribe!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Privacy Policy
    |--------------------------------------------------------------------------
    */

    public function policy()
    {
        if (view()->exists('privacy-policy')) {
            return view('privacy-policy');
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Terms of Service
    |--------------------------------------------------------------------------
    */

    public function terms()
    {
        if (view()->exists('terms-of-service')) {
            return view('terms-of-service');
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    public function security()
    {
        if (view()->exists('security')) {
            return view('security');
        }
        return view('404');
    }
}