<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Region;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class RegionsController extends Controller
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
    | Regions Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/regions')) {
            //Regions
            $regions = Region::with('division')->get();

            //Divisions
            $divisions = Division::all();

            return view('admin/regions', [
                'regions' => $regions,
                'divisions' => $divisions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Region Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:191',
            'division_id' => 'required|integer|exists:divisions,id',
        ]);

        try {
            //Role Create
            $region = Region::create([
                'name' => $request->name,
                'division_id' => $request->division_id
            ]);

            $encID = Crypt::encryptString($region->id);

            return response()->json([
                'success' => true,
                'region' => $region,
                'encID' => $encID,
                'message' => 'Region created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create region!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Region Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $regionID = Crypt::decryptString($id);

            $region = Region::findOrFail($regionID);

            return response()->json([
                'region' => $region,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get region!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Region Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Region ID
        $regionID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:191',
            'division_id' => 'required|integer|exists:divisions,id',
        ]);

        try {
            //Region
            $region = Region::findorfail($regionID);

            //region Update
            $region->name = $request->name;
            $region->division_id = $request->division_id;
            $region->save();

            return response()->json([
                'success' => true,
                'region' => $region,
                'message' => 'Region updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update region!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Region Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $regionID = Crypt::decryptString($id);

            $region = Region::findOrFail($regionID);
            $region->delete();

            return response()->json([
                'success' => true,
                'message' => 'Region deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete region!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Region Destroy Multiple
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

            Region::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Regions deleted successfully!'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete regions!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
