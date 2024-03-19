<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Language;
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

class LanguagesController extends Controller
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
    | Languages Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/languages')) {
            //Languages
            $languages = Language::all();

            return view('admin/languages', [
                'languages' => $languages
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Language Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Language Create
            $language = Language::create([                
                'name' => $request->name,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($language->id);

            return response()->json([
                'success' => true,
                'language' => $language,
                'encID' => $encID,
                'message' => 'Language created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create language!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Language Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $languageID = Crypt::decryptString($id);

            $language = Language::findOrFail($languageID);

            return response()->json([
                'language' => $language,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get language!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Language Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Language ID
        $languageID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Language
            $language = Language::findorfail($languageID);

            //Language Update
            $language->name = $request->name;
            $language->icon = $request->icon ?: null;
            $language->color = $request->color ?: null;
            $language->save();

            return response()->json([
                'success' => true,
                'language' => $language,
                'message' => 'Language updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update language!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Language Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $languageID = Crypt::decryptString($id);

            $language = Language::findOrFail($languageID);
            $language->delete();

            return response()->json([
                'success' => true,
                'message' => 'Language deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete language!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Language Destroy Multiple
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
    
            Language::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Languages deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete languages!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
