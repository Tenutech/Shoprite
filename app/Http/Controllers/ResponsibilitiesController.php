<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Responsibility;
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

class ResponsibilitiesController extends Controller
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
    | Responsibilities Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/responsibilities')) {
            //Responsibilities
            $responsibilities = Responsibility::all();

            //Positions
            $positions = Position::all();

            return view('admin/responsibilities', [
                'responsibilities' => $responsibilities,
                'positions' => $positions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Responsibility Add
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
            //Responsibility Create
            $responsibility = Responsibility::create([                
                'position_id' => $request->position_id ?: null,
                'description' => $request->description ?: null,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($responsibility->id);

            return response()->json([
                'success' => true,
                'responsibility' => $responsibility,
                'encID' => $encID,
                'message' => 'Responsibility created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create responsibility!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Responsibility Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $qualificationID = Crypt::decryptString($id);

            $responsibility = Responsibility::findOrFail($qualificationID);

            return response()->json([
                'responsibility' => $responsibility,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get responsibility!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Responsibility Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Responsibility ID
        $qualificationID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'description' => 'required|string'
        ]);

        try {
            //Responsibility
            $responsibility = Responsibility::findorfail($qualificationID);

            //Responsibility Update
            $responsibility->position_id = $request->position_id ?: null;
            $responsibility->description = $request->description ?: null;
            $responsibility->icon = $request->icon ?: null;
            $responsibility->color = $request->color ?: null;
            $responsibility->save();

            return response()->json([
                'success' => true,
                'responsibility' => $responsibility,
                'message' => 'Responsibility updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update responsibility!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Responsibility Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $qualificationID = Crypt::decryptString($id);

            $responsibility = Responsibility::findOrFail($qualificationID);
            $responsibility->delete();

            return response()->json([
                'success' => true,
                'message' => 'Responsibility deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete responsibility!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Responsibility Destroy Multiple
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
    
            Responsibility::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Responsibilities deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete responsibilities!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
