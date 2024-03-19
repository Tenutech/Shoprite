<?php

namespace App\Http\Controllers;

use Exception;
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

class BrandsController extends Controller
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
    | Brands Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/brands')) {
            //Brands
            $brands = Brand::all();

            return view('admin/brands', [
                'brands' => $brands
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Brand Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'avatar' => ['image' ,'mimes:jpg,jpeg,png,svg','max:1024'],
            'name' => 'required|string|max:191'
        ]);

        try {
            // Avatar
            if ($request->avatar) {               
                $avatar = request()->file('avatar');
                $avatarName = 'build/images/brands/'.strtolower($request->name).'.'.$avatar->getClientOriginalExtension();
                $avatarPath = public_path('build/images/brands/');
                $avatar->move($avatarPath, $avatarName);
            } else {
                $avatarName = 'build/images/brands/shoprite-logo.svg';
            }

            //Brand Create
            $brand = Brand::create([                
                'name' => $request->name,
                'icon' => $avatarName,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($brand->id);

            return response()->json([
                'success' => true,
                'brand' => $brand,
                'encID' => $encID,
                'message' => 'Brand created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create brand!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Brand Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $brandID = Crypt::decryptString($id);

            $brand = Brand::findOrFail($brandID);

            return response()->json([
                'brand' => $brand,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get brand!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Brand Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Brand ID
        $brandID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'avatar' => ['image' ,'mimes:jpg,jpeg,png','max:1024'],
            'name' => 'required|string|max:191'
        ]);

        try {
            //Brand
            $brand = Brand::findorfail($brandID);

            // Avatar
            if ($request->avatar) {
                // Check if a previous avatar exists and is not the default one
                if ($brand->image) {
                    // Construct the path to the old avatar
                    $oldAvatarPath = public_path($brand->image);
                    // Check if the file exists and delete it
                    if (File::exists($oldAvatarPath)) {
                        File::delete($oldAvatarPath);
                    }
                }
                
                $avatar = request()->file('avatar');
                $avatarName = 'build/images/brands/'.strtolower($request->name).'.'.$avatar->getClientOriginalExtension();
                $avatarPath = public_path('build/images/brands/');
                $avatar->move($avatarPath, $avatarName);
            } else {
                $avatarName = 'build/images/brands/shoprite-logo.svg';
            }

            //Brand Update
            $brand->name = $request->name;
            $brand->icon = $avatarName;
            $brand->color = $request->color ?: null;
            $brand->save();

            return response()->json([
                'success' => true,
                'brand' => $brand,
                'message' => 'Brand updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update brand!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Brand Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $brandID = Crypt::decryptString($id);

            $brand = Brand::findOrFail($brandID);
            $brand->delete();

            return response()->json([
                'success' => true,
                'message' => 'Brand deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete brand!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Brand Destroy Multiple
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
    
            Brand::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Brands deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete brands!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
