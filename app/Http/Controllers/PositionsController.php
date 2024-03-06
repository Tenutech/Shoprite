<?php

namespace App\Http\Controllers;

use Exception;
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

class PositionsController extends Controller
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
    | Positions Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/positions')) {
            //Positions
            $positions = Position::all();

            return view('admin/positions', [
                'positions' => $positions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Position Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'avatar' => ['image' ,'mimes:jpg,jpeg,png','max:1024'],
            'name' => 'required|string|max:191'
        ]);

        try {
            // Avatar
            if ($request->avatar) {               
                $avatar = request()->file('avatar');
                $avatarName = strtolower($request->name).'.'.$avatar->getClientOriginalExtension();
                $avatarPath = public_path('build/images/position/');
                $avatar->move($avatarPath, $avatarName);
            } else {
                $avatarName = 'build/images/position/assistant.jpg';
            }

            //Position Create
            $position = Position::create([                
                'name' => $request->name,
                'description' => $request->description && $request->description != '<p></p>'  && $request->description != '<p><br></p>' ? $request->description : null,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null,
                'image' => $avatarName
            ]);

            $encID = Crypt::encryptString($position->id);

            return response()->json([
                'success' => true,
                'position' => $position,
                'encID' => $encID,
                'message' => 'Position created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create position!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Position Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $positionID = Crypt::decryptString($id);

            $position = Position::findOrFail($positionID);

            return response()->json([
                'position' => $position,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get position!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Position Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Position ID
        $positionID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'avatar' => ['image' ,'mimes:jpg,jpeg,png','max:1024'],
            'name' => 'required|string|max:191'
        ]);

        try {
            //Position
            $position = Position::findorfail($positionID);

            // Avatar
            if ($request->avatar) {
                // Check if a previous avatar exists and is not the default one
                if ($position->image && $position->image !== 'build/images/position/assistant.jpg') {
                    // Construct the path to the old avatar
                    $oldAvatarPath = public_path($position->image);
                    // Check if the file exists and delete it
                    if (File::exists($oldAvatarPath)) {
                        File::delete($oldAvatarPath);
                    }
                }
                
                $avatar = request()->file('avatar');
                $avatarName = strtolower($request->name).'.'.$avatar->getClientOriginalExtension();
                $avatarPath = public_path('build/images/position/');
                $avatar->move($avatarPath, $avatarName);
            } else {
                $avatarName = 'build/images/position/assistant.jpg';
            }

            //Position Update
            $position->name = $request->name;
            $position->description = $request->description && $request->description != '<p></p>'  && $request->description != '<p><br></p>' ? $request->description : null;
            $position->icon = $request->icon ?: null;
            $position->color = $request->color ?: null;
            $position->image = $avatarName;
            $position->save();

            return response()->json([
                'success' => true,
                'position' => $position,
                'message' => 'Position updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update position!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Position Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $positionID = Crypt::decryptString($id);

            $position = Position::findOrFail($positionID);
            $position->delete();

            return response()->json([
                'success' => true,
                'message' => 'Position deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete position!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Position Destroy Multiple
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
    
            Position::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Positions deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete positions!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
