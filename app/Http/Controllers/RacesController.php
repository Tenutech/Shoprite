<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Race;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;

class RacesController extends Controller
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
    | Races Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/races')) {
            //Races
            $races = Race::all();

            return view('admin/races', [
                'races' => $races
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Race Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Race Create
            $race = Race::create([
                'name' => $request->name,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($race->id);

            return response()->json([
                'success' => true,
                'race' => $race,
                'encID' => $encID,
                'message' => 'Race created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create race!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Race Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $raceID = Crypt::decryptString($id);

            $race = Race::findOrFail($raceID);

            return response()->json([
                'race' => $race,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get race!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Race Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Race ID
        $raceID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Race
            $race = Race::findorfail($raceID);

            //Race Update
            $race->name = $request->name;
            $race->icon = $request->icon ?: null;
            $race->color = $request->color ?: null;
            $race->save();

            return response()->json([
                'success' => true,
                'race' => $race,
                'message' => 'Race updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update race!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Race Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $raceID = Crypt::decryptString($id);

            $race = Race::findOrFail($raceID);
            $race->delete();

            return response()->json([
                'success' => true,
                'message' => 'Race deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete race!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Race Destroy Multiple
    |--------------------------------------------------------------------------
    */

    public function destroyMultiple(Request $request)
    {
        try {
            $ids = $request->input('ids');

            if (is_null($ids) || empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No IDs provided',
                    'error' => 'No IDs provided'
                ], 400);
            }

            // Decrypt IDs
            $decryptedIds = array_map(function ($id) {
                return Crypt::decryptString($id);
            }, $ids);

            DB::beginTransaction();

            Race::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Races deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete races!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
