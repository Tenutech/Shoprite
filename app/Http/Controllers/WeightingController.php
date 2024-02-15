<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Applicant;
use App\Models\ScoreWeighting;
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

class WeightingController extends Controller
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
    | Weighting Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/weighting')) {
            //Weightings
            $weightings = ScoreWeighting::all();

            // Calculate total weight
            $totalWeight = $weightings->sum('weight');

            // Score Types
            $applicantModel = new Applicant();
            $scoreTypes = $applicantModel->getFillable();

            return view('admin/weighting', [
                'weightings' => $weightings,
                'totalWeight' => $totalWeight,
                'scoreTypes' => $scoreTypes,
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Weighting Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'score_type' => 'required|string|max:191',
            'weight' => 'required|numeric|between:0,999999.99',
            'max_value' => 'nullable|numeric|between:0,999999.99',
            'condition_field' => 'nullable|string|max:191',
            'condition_value' => 'nullable|string|max:191',
            'fallback_value' => 'nullable|numeric|between:0,999999.99'
        ]);

        try {            
            //Weighting Create
            $weighting = ScoreWeighting::create([                
                'score_type' => $request->score_type,
                'weight' => $request->weight,
                'max_value' => $request->max_value ?: null,
                'condition_field' => $request->condition_field ?: null,
                'condition_value' => $request->condition_value ?: null,
                'fallback_value' => $request->fallback_value
            ]);

            $encID = Crypt::encryptString($weighting->id);

            //Weightings
            $weightings = ScoreWeighting::all();

            // Calculate total weight
            $totalWeight = $weightings->sum('weight');

            return response()->json([
                'success' => true,
                'weighting' => $weighting,
                'encID' => $encID,
                'totalWeight' => $totalWeight,
                'message' => 'Weighting created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create weighting!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Weighting Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $weightingID = Crypt::decryptString($id);

            $weighting = ScoreWeighting::findOrFail($weightingID);

            return response()->json([
                'weighting' => $weighting,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Weighting Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Weighting ID
        $weightingID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'score_type' => 'required|string|max:191',
            'weight' => 'required|numeric|between:0,999999.99',
            'max_value' => 'nullable|numeric|between:0,999999.99',
            'condition_field' => 'nullable|string|max:191',
            'condition_value' => 'nullable|string|max:191',
            'fallback_value' => 'nullable|numeric|between:0,999999.99'
        ]);

        try {
            //Weighting
            $weighting = ScoreWeighting::findorfail($weightingID);

            //Weighting Update
            $weighting->score_type = $request->score_type;
            $weighting->weight = $request->weight;
            $weighting->max_value = $request->max_value ?: null;
            $weighting->condition_field = $request->condition_field ?: null;
            $weighting->condition_value = $request->condition_value ?: null;
            $weighting->fallback_value = $request->fallback_value;
            $weighting->save();

            //Weightings
            $weightings = ScoreWeighting::all();

            // Calculate total weight
            $totalWeight = $weightings->sum('weight');

            return response()->json([
                'success' => true,
                'totalWeight' => $totalWeight,
                'message' => 'Weighting updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update weighting!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Weighting Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $weightingID = Crypt::decryptString($id);

            $weighting = ScoreWeighting::findOrFail($weightingID);
            $weighting->delete();

            return response()->json([
                'success' => true,
                'message' => 'Weighting deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete weighting!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Weighting Destroy Multiple
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
            $decryptedIds = array_map(function($id) {
                return Crypt::decryptString($id);
            }, $ids);
    
            DB::beginTransaction();
    
            ScoreWeighting::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Weighting deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete weightings!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
