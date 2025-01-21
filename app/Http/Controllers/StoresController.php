<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Town;
use App\Models\Store;
use App\Models\Brand;
use App\Models\Region;
use App\Models\Division;
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

class StoresController extends Controller
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
    | Stores Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/stores')) {
            //Stores
            $stores = Store::with([
                'brand',
                'town',
                'region',
                'division'
            ])->orderByRaw('CAST(code AS UNSIGNED) ASC')->get();

            //Brands
            $brands = Brand::all();

            //Towns
            $towns = Town::all();

            //Regions
            $regions = Region::all();

            //Divisions
            $divisions = Division::all();

            return view('admin/stores', [
                'stores' => $stores,
                'brands' => $brands,
                'towns' => $towns,
                'regions' => $regions,
                'divisions' => $divisions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Store Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'code' => 'required|digits:4',
            'code_5' => 'required|digits:5',
            'code_6' => 'required|digits:6',
            'brand' => 'required|integer|min:1|exists:brands,id',
            'town' => 'required|integer|min:1|exists:towns,id',
            'region' => 'required|integer|min:1|exists:regions,id',
            'division' => 'required|integer|min:1|exists:divisions,id',
            'address' => 'required|nullable|string|max:255',
            'coordinates' => 'required|nullable|string|max:255'
        ]);

        try {
            //Store Create
            $store = Store::create([
                'code' => $request->code,
                'code_5' => $request->code_5,
                'code_6' => $request->code_6,
                'brand_id' => $request->brand,
                'town_id' => $request->town,
                'region_id' => $request->region,
                'division_id' => $request->division,
                'address' => $request->address ?: null,
                'coordinates' => $request->coordinates ?: null
            ]);

            $encID = Crypt::encryptString($store->id);

            return response()->json([
                'success' => true,
                'store' => $store,
                'encID' => $encID,
                'message' => 'Store created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create store!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Store Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $storeID = Crypt::decryptString($id);

            $store = Store::findOrFail($storeID);

            return response()->json([
                'store' => $store,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get store!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Store Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Store ID
        $storeID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'code' => 'required|digits:4',
            'code_5' => 'required|digits:5',
            'code_6' => 'required|digits:6',
            'brand' => 'required|integer|min:1|exists:brands,id',
            'town' => 'required|integer|min:1|exists:towns,id',
            'region' => 'required|integer|min:1|exists:regions,id',
            'division' => 'required|integer|min:1|exists:divisions,id',
            'address' => 'nullable|string|max:255',
            'coordinates' => 'nullable|string|max:255'
        ]);

        try {
            //Store
            $store = Store::findorfail($storeID);

            //Store Update
            $store->code = $request->code;
            $store->code_5 = $request->code_5;
            $store->code_6 = $request->code_6;
            $store->brand_id = $request->brand;
            $store->town_id = $request->town;
            $store->region_id = $request->region;
            $store->division_id = $request->division;
            $store->address = $request->address ?: null;
            $store->coordinates = $request->coordinates ?: null;
            $store->save();

            return response()->json([
                'success' => true,
                'store' => $store,
                'message' => 'Store updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update store!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Store Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $storeID = Crypt::decryptString($id);

            $store = Store::findOrFail($storeID);
            $store->delete();

            return response()->json([
                'success' => true,
                'message' => 'Store deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete store!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Store Destroy Multiple
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

            Store::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stores deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete stores!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
