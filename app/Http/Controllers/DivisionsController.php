<?php

namespace App\Http\Controllers;

use App\Exports\DivisionsExport;
use Exception;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class DivisionsController extends Controller
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
    | Divisions Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/divisions')) {
            //Regions
            $divisions = Division::all();

            return view('admin/divisions', [
                'divisions' => $divisions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Division Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Division Create
            $division = Division::create([
                'name' => $request->name
            ]);

            $encID = Crypt::encryptString($division->id);

            return response()->json([
                'success' => true,
                'division' => $division,
                'encID' => $encID,
                'message' => 'Division created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create division!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Division Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $divisionID = Crypt::decryptString($id);

            $division = Division::findOrFail($divisionID);

            return response()->json([
                'division' => $division,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get division!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Division Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Region ID
        $divisionID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Division
            $division = Division::findorfail($divisionID);

            //Division Update
            $division->name = $request->name;
            $division->save();

            return response()->json([
                'success' => true,
                'division' => $division,
                'message' => 'Division updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update division!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Division Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $divisionID = Crypt::decryptString($id);

            $division = Division::findOrFail($divisionID);
            $division->delete();

            return response()->json([
                'success' => true,
                'message' => 'Division deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete division!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Division Destroy Multiple
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

            Division::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Divisions deleted successfully!'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete divisions!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Summary of export
     * @param \Illuminate\Http\Request $request
     */
    public function export(Request $request)
    {
        $search = $request->input('search');

        return Excel::download(new DivisionsExport($search), 'divisions_report.xlsx');
    }
}
