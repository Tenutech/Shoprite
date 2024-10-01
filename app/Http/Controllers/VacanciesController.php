<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Store;
use App\Models\Vacancy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

class VacanciesController extends Controller
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
    | Vacancies Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        if (view()->exists('manager/vacancies')) {
            //User ID
            $userID = Auth::id();

            //User
            $user = User::with('vacancies')->findOrFail($userID);

            //Vacancies
            $vacancies = Vacancy::with([
                'user',
                'position',
                'store.brand',
                'store.town',
                'type',
                'status',
                'sapNumbers',
                'appointed.latestInterview'
            ])
            ->where('user_id', $userID)
            ->orderBy('created_at', 'desc')
            ->get();

            //Store
            $store = Store::with([
                'brand',
                'town',
                'region',
                'division'
            ])
            ->where('id', $user->store_id)
            ->first();

            return view('manager/vacancies', [
                'user' => $user,
                'vacancies' => $vacancies,
                'store' => $store
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Vacancies
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
            'savedBy' => function ($query) use ($userID) {
                $query->where('user_id', $userID);
            }
        ])
        ->whereIn('advertisement', $advertisement)
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
}
