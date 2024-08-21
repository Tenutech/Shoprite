<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Reason;
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

class ReasonsController extends Controller
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
    | Reasons Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/reasons')) {
            //Reasons
            $reasons = Reason::all();

            return view('admin/reasons', [
                'reasons' => $reasons
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Reason Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Reason Create
            $reason = Reason::create([
                'name' => $request->name,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($reason->id);

            return response()->json([
                'success' => true,
                'reason' => $reason,
                'encID' => $encID,
                'message' => 'Reason created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reason!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reason Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $reasonID = Crypt::decryptString($id);

            $reason = Reason::findOrFail($reasonID);

            return response()->json([
                'reason' => $reason,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get reason!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reason Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Reason ID
        $reasonID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Reason
            $reason = Reason::findorfail($reasonID);

            //Reason Update
            $reason->name = $request->name;
            $reason->icon = $request->icon ?: null;
            $reason->color = $request->color ?: null;
            $reason->save();

            return response()->json([
                'success' => true,
                'reason' => $reason,
                'message' => 'Reason updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reason!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reason Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $reasonID = Crypt::decryptString($id);

            $reason = Reason::findOrFail($reasonID);
            $reason->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reason deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reason!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reason Destroy Multiple
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

            Reason::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reasons deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reasons!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
