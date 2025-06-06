<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Education;
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

class EducationsController extends Controller
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
    | Educations Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/educations')) {
            //Educations
            $educations = Education::all();

            return view('admin/educations', [
                'educations' => $educations
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Education Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Education Create
            $education = Education::create([
                'name' => $request->name,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($education->id);

            return response()->json([
                'success' => true,
                'education' => $education,
                'encID' => $encID,
                'message' => 'Education created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create education!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Education Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $EducationID = Crypt::decryptString($id);

            $education = Education::findOrFail($EducationID);

            return response()->json([
                'education' => $education,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get education!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Education Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Education ID
        $EducationID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Education
            $education = Education::findorfail($EducationID);

            //Education Update
            $education->name = $request->name;
            $education->icon = $request->icon ?: null;
            $education->color = $request->color ?: null;
            $education->save();

            return response()->json([
                'success' => true,
                'education' => $education,
                'message' => 'Education updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update education!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Education Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $EducationID = Crypt::decryptString($id);

            $education = Education::findOrFail($EducationID);
            $education->delete();

            return response()->json([
                'success' => true,
                'message' => 'Education deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete education!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Education Destroy Multiple
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

            Education::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Educations deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete educations!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
