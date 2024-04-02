<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\ExperienceRequirement;
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

class ExperienceController extends Controller
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
    | Experience Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/experience')) {
            //Experience
            $experiences = ExperienceRequirement::all();

            //Positions
            $positions = Position::all();

            return view('admin/experience', [
                'experiences' => $experiences,
                'positions' => $positions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Experience Add
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
            //Experience Create
            $experience = ExperienceRequirement::create([                
                'position_id' => $request->position_id ?: null,
                'description' => $request->description ?: null,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($experience->id);

            return response()->json([
                'success' => true,
                'experience' => $experience,
                'encID' => $encID,
                'message' => 'Experience requirement created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create experience requirement!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Experience Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $experienceID = Crypt::decryptString($id);

            $experience = ExperienceRequirement::findOrFail($experienceID);

            return response()->json([
                'experience' => $experience,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get experience requirement!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Experience Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Experience ID
        $experienceID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'description' => 'required|string'
        ]);

        try {
            //Experience
            $experience = ExperienceRequirement::findorfail($experienceID);

            //Experience Update
            $experience->position_id = $request->position_id ?: null;
            $experience->description = $request->description ?: null;
            $experience->icon = $request->icon ?: null;
            $experience->color = $request->color ?: null;
            $experience->save();

            return response()->json([
                'success' => true,
                'experience' => $experience,
                'message' => 'Experience requirement updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update experience requirement!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Experience Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $experienceID = Crypt::decryptString($id);

            $experience = ExperienceRequirement::findOrFail($experienceID);
            $experience->delete();

            return response()->json([
                'success' => true,
                'message' => 'Experience requirement deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete experience requirement!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Experience Destroy Multiple
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
    
            ExperienceRequirement::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Experience requirements deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete experience requirements!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
