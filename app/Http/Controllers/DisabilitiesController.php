<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Disability;
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

class DisabilitiesController extends Controller
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
    | Disabilities Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/disabilities')) {
            //Disabilities
            $disabilities = Disability::all();

            return view('admin/disabilities', [
                'disabilities' => $disabilities
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Disability Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Disability Create
            $disability = Disability::create([
                'name' => $request->name,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($disability->id);

            return response()->json([
                'success' => true,
                'disability' => $disability,
                'encID' => $encID,
                'message' => 'Disability created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create disability!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Disability Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $disabilityID = Crypt::decryptString($id);

            $disability = Disability::findOrFail($disabilityID);

            return response()->json([
                'disability' => $disability,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get disability!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Disability Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Disability ID
        $disabilityID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Disability
            $disability = Disability::findorfail($disabilityID);

            //Disability Update
            $disability->name = $request->name;
            $disability->icon = $request->icon ?: null;
            $disability->color = $request->color ?: null;
            $disability->save();

            return response()->json([
                'success' => true,
                'disability' => $disability,
                'message' => 'Disability updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update disability!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Disability Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $disabilityID = Crypt::decryptString($id);

            $disability = Disability::findOrFail($disabilityID);
            $disability->delete();

            return response()->json([
                'success' => true,
                'message' => 'Disability deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete disability!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Disability Destroy Multiple
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

            Disability::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Disabilities deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete disabilities!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
