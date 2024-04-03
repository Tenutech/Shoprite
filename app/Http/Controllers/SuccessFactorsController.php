<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\SuccessFactor;
use App\Models\Position;
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

class SuccessFactorsController extends Controller
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
    | Success Factors Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/success-factors')) {
            //Success Factors
            $successFactors = SuccessFactor::orderBy('position_id')->get();
            
            //Positions
            $positions = Position::all();

            return view('admin/success-factors', [
                'successFactors' => $successFactors,
                'positions' => $positions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Success Factor Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'description' => 'required|string'
        ]);

        try {
            //Success Factor Create
            $successFactor = SuccessFactor::create([                
                'position_id' => $request->position_id ?: null,
                'description' => $request->description ?: null,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($successFactor->id);

            return response()->json([
                'success' => true,
                'successFactor' => $successFactor,
                'encID' => $encID,
                'message' => 'Success factor created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create success factor!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Success Factor Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $successFactorID = Crypt::decryptString($id);

            $successFactor = SuccessFactor::findOrFail($successFactorID);

            return response()->json([
                'successFactor' => $successFactor,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get success factor!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Success Factor Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Success Factor ID
        $successFactorID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'description' => 'required|string'
        ]);

        try {
            //Success Factor
            $successFactor = SuccessFactor::findorfail($successFactorID);

            //Success Factor Update
            $successFactor->position_id = $request->position_id ?: null;
            $successFactor->description = $request->description ?: null;
            $successFactor->icon = $request->icon ?: null;
            $successFactor->color = $request->color ?: null;
            $successFactor->save();

            return response()->json([
                'success' => true,
                'successFactor' => $successFactor,
                'message' => 'Success factor updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update success factor!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Success Factor Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $successFactorID = Crypt::decryptString($id);

            $successFactor = SuccessFactor::findOrFail($successFactorID);
            $successFactor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Success factor deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete success factor!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Success Factor Destroy Multiple
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
    
            SuccessFactor::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Success factors deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete success factors!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
