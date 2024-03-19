<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Town;
use App\Models\Store;
use App\Models\Brand;
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
                'town'
            ])->get();

            //Brands
            $brands = Brand::all();

            //Towns
            $towns = Town::all();

            return view('admin/stores', [
                'stores' => $stores,
                'brands' => $brands,
                'towns' => $towns
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
            'brand' => 'required|integer|min:1|exists:brands,id',
            'town' => 'required|integer|min:1|exists:towns,id',
            'address' => 'sometimes|nullable|string|max:255'
        ]);

        try {
            //Store Create
            $store = Store::create([                
                'brand_id' => $request->brand,
                'town_id' => $request->town,
                'address' => $request->address ?: null
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
            'brand' => 'required|integer|min:1|exists:brands,id',
            'town' => 'required|integer|min:1|exists:towns,id',
            'address' => 'sometimes|nullable|string|max:255'
        ]);


        try {
            //Store
            $store = Store::findorfail($storeID);

            //Store Update
            $store->brand_id = $request->brand;
            $store->town_id = $request->town;
            $store->address = $request->address ?: null;
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
            $decryptedIds = array_map(function($id) {
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
