<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Applicant;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

class SaveController extends Controller
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
    | Applicant Save
    |--------------------------------------------------------------------------
    */

    public function applicantSave($id)
    {
        try {
            //User ID
            $userID = Auth::id();

            //Applicant ID
            $applicantID = Crypt::decryptString($id);

            // Fetch the applicant from the database
            $applicant = Applicant::findOrFail($applicantID);

            // Check if the user has already saved this opportunity
            if ($applicant->savedBy()->where('user_id', $userID)->exists()) {
                // If saved, unsave
                $applicant->savedBy()->detach($userID);
                return response()->json([
                    'userID' => $userID,
                    'success' => true,
                    'message' => 'Applicant Unsaved!'
                ], 200);
            } else {
                // If not saved, save
                $applicant->savedBy()->attach($userID);
                return response()->json([
                    'userID' => $userID,
                    'success' => true,
                    'message' => 'Applicant Saved!'
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed To Save Applicant!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Vacancy Save
    |--------------------------------------------------------------------------
    */

    public function vacancySave($id)
    {
        try {
            //User ID
            $userID = Auth::id();

            //Vacancy ID
            $vacancyID = Crypt::decryptString($id);

            // Fetch the applicant from the database
            $vacancy = Vacancy::with([
                'position',
                'store.brand',
                'store.town',
                'type'
            ])->findOrFail($vacancyID);

            // Check if the user has already saved this opportunity
            if ($vacancy->savedBy()->where('user_id', $userID)->exists()) {
                // If saved, unsave
                $vacancy->savedBy()->detach($userID);
                return response()->json([
                    'success' => true,
                    'vacancy' => $vacancy,
                    'id' => $id,
                    'message' => 'Vacancy Unsaved!'
                ], 200);
            } else {
                // If not saved, save
                $vacancy->savedBy()->attach($userID);
                return response()->json([
                    'success' => true,
                    'vacancy' => $vacancy,
                    'id' => $id,
                    'message' => 'Vacancy Saved!'
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed To Save Vacancy!',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
