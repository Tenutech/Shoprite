<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Duration;
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

class DurationsController extends Controller
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
    | Durations Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/durations')) {
            //Durations
            $durations = Duration::all();

            return view('admin/durations', [
                'durations' => $durations
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Duration Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Duration Create
            $duration = Duration::create([
                'name' => $request->name,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($duration->id);

            return response()->json([
                'success' => true,
                'duration' => $duration,
                'encID' => $encID,
                'message' => 'Duration created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create duration!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Duration Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $durationID = Crypt::decryptString($id);

            $duration = Duration::findOrFail($durationID);

            return response()->json([
                'duration' => $duration,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get duration!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Duration Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Duration ID
        $durationID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Duration
            $duration = Duration::findorfail($durationID);

            //Duration Update
            $duration->name = $request->name;
            $duration->icon = $request->icon ?: null;
            $duration->color = $request->color ?: null;
            $duration->save();

            return response()->json([
                'success' => true,
                'duration' => $duration,
                'message' => 'Duration updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update duration!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Duration Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $durationID = Crypt::decryptString($id);

            $duration = Duration::findOrFail($durationID);
            $duration->delete();

            return response()->json([
                'success' => true,
                'message' => 'Duration deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete duration!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Duration Destroy Multiple
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

            Duration::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Durations deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete durations!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
