<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Qualification;
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

class HoursController extends Controller
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
    | Working Hours Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/hours')) {
            //Working Hours
            $hours = Qualification::orderBy('position_id')->get();

            //Positions
            $positions = Position::all();

            return view('admin/hours', [
                'hours' => $hours,
                'positions' => $positions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Working Hour Add
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
            //Working Hour Create
            $hour = Qualification::create([
                'position_id' => $request->position_id ?: null,
                'description' => $request->description ?: null,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($hour->id);

            return response()->json([
                'success' => true,
                'hour' => $hour,
                'encID' => $encID,
                'message' => 'Working hour created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create working hour!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Working Hour Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $hourID = Crypt::decryptString($id);

            $hour = Qualification::findOrFail($hourID);

            return response()->json([
                'hour' => $hour,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get working hour!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Working Hour Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Working Hour ID
        $hourID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'description' => 'required|string'
        ]);

        try {
            //Working Hour
            $hour = Qualification::findorfail($hourID);

            //Working Hour Update
            $hour->position_id = $request->position_id ?: null;
            $hour->description = $request->description ?: null;
            $hour->icon = $request->icon ?: null;
            $hour->color = $request->color ?: null;
            $hour->save();

            return response()->json([
                'success' => true,
                'hour' => $hour,
                'message' => 'Working hour updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update working hour!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Working Hour Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $hourID = Crypt::decryptString($id);

            $hour = Qualification::findOrFail($hourID);
            $hour->delete();

            return response()->json([
                'success' => true,
                'message' => 'Working hour deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete working hour!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Working Hour Destroy Multiple
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

            Qualification::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Working hours deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete working hours!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
