<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Town;
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

class TownsController extends Controller
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
    | Towns Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/towns')) {
            //Towns
            $towns = Town::with('province')->get();

            //Provinces
            $provinces = Province::all();

            return view('admin/towns', [
                'towns' => $towns,
                'provinces' => $provinces
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Town Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'sometimes|nullable|string|max:10',
            'province' => 'required|integer|min:1|exists:provinces,id',
            'district' => 'sometimes|nullable|string|max:100',
            'seat' => 'sometimes|nullable|string|max:100',
            'class' => 'sometimes|nullable|string|max:2',
        ]);

        try {
            //Town Create
            $town = Town::create([
                'name' => $request->name,
                'code' => $request->code ?: null,
                'province_id' => $request->province,
                'district' => $request->district ?: null,
                'seat' => $request->seat ?: null,
                'class' => $request->class ?: null,
            ]);

            $encID = Crypt::encryptString($town->id);

            return response()->json([
                'success' => true,
                'town' => $town,
                'encID' => $encID,
                'message' => 'Town created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create town!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Town Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $townID = Crypt::decryptString($id);

            $town = Town::findOrFail($townID);

            return response()->json([
                'town' => $town,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get town!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Town Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Town ID
        $townID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'sometimes|nullable|string|max:10',
            'province' => 'required|integer|min:1|exists:provinces,id',
            'district' => 'sometimes|nullable|string|max:100',
            'seat' => 'sometimes|nullable|string|max:100',
            'class' => 'sometimes|nullable|string|max:2',
        ]);

        try {
            //Town
            $town = Town::findorfail($townID);

            //Town Update
            $town->name = $request->name;
            $town->code = $request->code ?: null;
            $town->province_id = $request->province;
            $town->district = $request->district ?: null;
            $town->seat = $request->seat ?: null;
            $town->class = $request->class ?: null;
            $town->save();

            return response()->json([
                'success' => true,
                'town' => $town,
                'message' => 'Town updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update town!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Town Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $townID = Crypt::decryptString($id);

            $town = Town::findOrFail($townID);
            $town->delete();

            return response()->json([
                'success' => true,
                'message' => 'Town deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete town!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Town Destroy Multiple
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

            Town::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Towns deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete towns!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
