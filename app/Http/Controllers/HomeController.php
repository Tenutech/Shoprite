<?php

namespace App\Http\Controllers;

use Exception;
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
use App\Models\Subscription;
use App\Models\Notification;
use App\Models\Retrenchment;
use App\Models\Application;
use App\Models\ChatTemplate;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'applicant.brands',
                'appliedVacancies'
            ])
            ->withCount('appliedVacancies')
            ->findOrFail($userID);

            // Type
            $types = Type::get();

            // Race
            $races = Race::get();

            // Education
            $educations = Education::where('id', '!=', 3)->get();

            // Duration
            $durations = Duration::get();

            // Brand
            $brands = Brand::whereIn('id', [1, 2, 5, 6])->get();

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

            //Situational
            $situationalQuestions = ChatTemplate::whereHas('state', function ($query) {
                $query->whereIn('name', ['situational']);
            })
            ->inRandomOrder()
            ->get();

            return view('home', [
                'userID' => $userID,
                'user' => $user,
                'types' => $types,
                'races' => $races,
                'educations' => $educations,
                'durations' => $durations,
                'brands' => $brands,
                'literacyQuestions' => $literacyQuestions,
                'numeracyQuestions' => $numeracyQuestions,
                'situationalQuestions' => $situationalQuestions
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

        // Determine advertisement scope conditions based on the user's internal flag
        $advertisement = $user->internal == 1 ? ['Any', 'Internal'] : ['Any', 'External'];

        //Vacancies
        $vacancies = Vacancy::with([
            'user',
            'position.tags',
            'store.brand',
            'store.town',
            'type',
            'status',
            'applicants',
            'applications',
            'savedBy' => function ($query) use ($userID) {
                $query->where('user_id', $userID);
            }
        ])
        ->withCount(['applications as total_applications'])
        ->withCount(['applications as applications_approved' => function ($query) {
            $query->where('approved', 'Yes');
        }])
        ->withCount(['applications as applications_rejected' => function ($query) {
            $query->where('approved', 'No');
        }])
        ->whereIn('advertisement', $advertisement)
        ->where('status_id', 2)
        ->where('deleted', 'No')
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
