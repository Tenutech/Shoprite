<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Gender;
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

class GendersController extends Controller
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
    | Genders Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/genders')) {
            //Genders
            $genders = Gender::all();

            return view('admin/genders', [
                'genders' => $genders
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Gender Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Gender Create
            $gender = Gender::create([
                'name' => $request->name,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($gender->id);

            return response()->json([
                'success' => true,
                'gender' => $gender,
                'encID' => $encID,
                'message' => 'Gender created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create gender!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Gender Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $genderID = Crypt::decryptString($id);

            $gender = Gender::findOrFail($genderID);

            return response()->json([
                'gender' => $gender,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get gender!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Gender Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Gender ID
        $genderID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Gender
            $gender = Gender::findorfail($genderID);

            //Gender Update
            $gender->name = $request->name;
            $gender->icon = $request->icon ?: null;
            $gender->color = $request->color ?: null;
            $gender->save();

            return response()->json([
                'success' => true,
                'gender' => $gender,
                'message' => 'Gender updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update gender!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Gender Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $genderID = Crypt::decryptString($id);

            $gender = Gender::findOrFail($genderID);
            $gender->delete();

            return response()->json([
                'success' => true,
                'message' => 'Gender deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete gender!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Gender Destroy Multiple
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

            Gender::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Genders deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete genders!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
