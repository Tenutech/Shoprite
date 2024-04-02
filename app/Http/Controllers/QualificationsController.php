<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Qualification;
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

class QualificationsController extends Controller
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
    | Qualifications Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/qualifications')) {
            //Qualifications
            $qualifications = Qualification::all();

            //Positions
            $positions = Position::all();

            return view('admin/qualifications', [
                'qualifications' => $qualifications,
                'positions' => $positions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Qualification Add
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
            //Qualification Create
            $qualification = Qualification::create([                
                'position_id' => $request->position_id ?: null,
                'description' => $request->description ?: null,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($qualification->id);

            return response()->json([
                'success' => true,
                'qualification' => $qualification,
                'encID' => $encID,
                'message' => 'Qualification created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create qualification!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Qualification Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $qualificationID = Crypt::decryptString($id);

            $qualification = Qualification::findOrFail($qualificationID);

            return response()->json([
                'qualification' => $qualification,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get qualification!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Qualification Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Qualification ID
        $qualificationID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'description' => 'required|string'
        ]);

        try {
            //Qualification
            $qualification = Qualification::findorfail($qualificationID);

            //Qualification Update
            $qualification->position_id = $request->position_id ?: null;
            $qualification->description = $request->description ?: null;
            $qualification->icon = $request->icon ?: null;
            $qualification->color = $request->color ?: null;
            $qualification->save();

            return response()->json([
                'success' => true,
                'qualification' => $qualification,
                'message' => 'Qualification updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update qualification!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Qualification Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $qualificationID = Crypt::decryptString($id);

            $qualification = Qualification::findOrFail($qualificationID);
            $qualification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Qualification deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete qualification!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Qualification Destroy Multiple
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
    
            Qualification::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Qualifications deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete qualifications!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
