<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\PhysicalRequirement;
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

class PhysicalController extends Controller
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
    | Physical Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/physical')) {
            //Physical
            $physicals = PhysicalRequirement::all();

            //Positions
            $positions = Position::all();

            return view('admin/physical', [
                'physicals' => $physicals,
                'positions' => $positions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Physical Add
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
            //Physical Create
            $physical = PhysicalRequirement::create([                
                'position_id' => $request->position_id ?: null,
                'description' => $request->description ?: null,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($physical->id);

            return response()->json([
                'success' => true,
                'physical' => $physical,
                'encID' => $encID,
                'message' => 'Physical requirement created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create physical requirement!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Physical Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $physicalID = Crypt::decryptString($id);

            $physical = PhysicalRequirement::findOrFail($physicalID);

            return response()->json([
                'physical' => $physical,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get physical requirement!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Physical Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Physical ID
        $physicalID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'description' => 'required|string'
        ]);

        try {
            //Physical
            $physical = PhysicalRequirement::findorfail($physicalID);

            //Physical Update
            $physical->position_id = $request->position_id ?: null;
            $physical->description = $request->description ?: null;
            $physical->icon = $request->icon ?: null;
            $physical->color = $request->color ?: null;
            $physical->save();

            return response()->json([
                'success' => true,
                'physical' => $physical,
                'message' => 'Physical requirement updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update physical requirement!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Physical Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $physicalID = Crypt::decryptString($id);

            $physical = PhysicalRequirement::findOrFail($physicalID);
            $physical->delete();

            return response()->json([
                'success' => true,
                'message' => 'Physical requirement deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete physical requirement!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Physical Destroy Multiple
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
    
            PhysicalRequirement::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Physical requirements deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete physical requirements!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
