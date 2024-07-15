<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Skill;
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

class SkillsController extends Controller
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
    | Skills Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/skills')) {
            //Skills
            $skills = Skill::orderBy('position_id')->get();
            
            //Positions
            $positions = Position::all();

            return view('admin/skills', [
                'skills' => $skills,
                'positions' => $positions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Skill Add
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
            //Skill Create
            $skill = Skill::create([                
                'position_id' => $request->position_id ?: null,
                'description' => $request->description ?: null,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($skill->id);

            return response()->json([
                'success' => true,
                'skill' => $skill,
                'encID' => $encID,
                'message' => 'Skill created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create skill!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Skill Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $skillID = Crypt::decryptString($id);

            $skill = Skill::findOrFail($skillID);

            return response()->json([
                'skill' => $skill,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get skill!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Skill Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Skill ID
        $skillID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'description' => 'required|string'
        ]);

        try {
            //Skill
            $skill = Skill::findorfail($skillID);

            //Skill Update
            $skill->position_id = $request->position_id ?: null;
            $skill->description = $request->description ?: null;
            $skill->icon = $request->icon ?: null;
            $skill->color = $request->color ?: null;
            $skill->save();

            return response()->json([
                'success' => true,
                'skill' => $skill,
                'message' => 'Skill updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update skill!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Skill Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $skillID = Crypt::decryptString($id);

            $skill = Skill::findOrFail($skillID);
            $skill->delete();

            return response()->json([
                'success' => true,
                'message' => 'Skill deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete skill!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Skill Destroy Multiple
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
    
            Skill::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Skills deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete skills!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
