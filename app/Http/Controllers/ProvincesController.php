<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Province;
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

class ProvincesController extends Controller
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
    | Provinces Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/provinces')) {
            //Provinces
            $provinces = Province::all();

            return view('admin/provinces', [
                'provinces' => $provinces
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Province Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            //Province Create
            $province = Province::create([
                'name' => $request->name
            ]);

            $encID = Crypt::encryptString($province->id);

            return response()->json([
                'success' => true,
                'province' => $province,
                'encID' => $encID,
                'message' => 'Province created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create province!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Province Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $provinceID = Crypt::decryptString($id);

            $province = Province::findOrFail($provinceID);

            return response()->json([
                'province' => $province,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get province!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Province Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Province ID
        $provinceID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            //Province
            $province = Province::findorfail($provinceID);

            //Province Update
            $province->name = $request->name;
            $province->save();

            return response()->json([
                'success' => true,
                'province' => $province,
                'message' => 'Province updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update province!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Province Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $provinceID = Crypt::decryptString($id);

            $province = Province::findOrFail($provinceID);
            $province->delete();

            return response()->json([
                'success' => true,
                'message' => 'Province deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete province!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Province Destroy Multiple
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

            Province::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Provinces deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete provinces!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
