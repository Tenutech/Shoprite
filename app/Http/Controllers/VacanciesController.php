<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Town;
use App\Models\Type;
use App\Models\Brand;
use App\Models\Vacancy;
use App\Models\Position;
use App\Models\Province;
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
        if (view()->exists('vacancies')) {
            //Positions
            $positions = Position::whereNotIn('id', [1, 10])->get();

            //Selected Position
            $selectedPositionId = null;

            if ($request->has('position')) {
                $positionName = $request->input('position');
                $position = Position::where('name', $positionName)->first();

                if($position) {
                    $selectedPositionId = $position->id;
                }
            }

            //Types
            $types = Type::get();

            //Brands
            $brands = Brand::get();

            //Towns
            $towns = Town::get();

            //Provinces
            $provinces = Province::get();

            //Selected Province
            $selectedProvinceId = null;

            if ($request->has('province')) {
                $provinceName = $request->input('province');
                $province = Province::where('name', $provinceName)->first();

                if($province) {
                    $selectedProvinceId = $province->id;
                }
            }

            return view('vacancies',[
                'positions' => $positions,
                'selectedPositionId' => $selectedPositionId,
                'types' => $types,
                'brands' => $brands,                
                'towns' => $towns,
                'provinces' => $provinces,
                'selectedProvinceId' => $selectedProvinceId,
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
}