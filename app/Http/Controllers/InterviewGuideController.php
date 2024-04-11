<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\InterviewQuestion;
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

class InterviewGuideController extends Controller
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
    | Interview Questions Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/interview-guides')) {
            //Interview Questions
            $guides = InterviewQuestion::all();

            return view('admin/interview-guides', [
                'guides' => $guides
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Question Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'question' => 'required|string'
        ]);

        try {
            //Interview Question Create
            $guide = InterviewQuestion::create([                
                'question' => $request->question,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($guide->id);

            return response()->json([
                'success' => true,
                'guide' => $guide,
                'encID' => $encID,
                'message' => 'Guide created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create guide!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Question Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $guideID = Crypt::decryptString($id);

            $guide = InterviewQuestion::findOrFail($guideID);

            return response()->json([
                'guide' => $guide,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get guide!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Question Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //InterviewQuestion ID
        $guideID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'question' => 'required|string'
        ]);

        try {
            //Interview Question
            $guide = InterviewQuestion::findorfail($guideID);

            //Interview Question Update
            $guide->question = $request->question;
            $guide->icon = $request->icon ?: null;
            $guide->color = $request->color ?: null;
            $guide->save();

            return response()->json([
                'success' => true,
                'guide' => $guide,
                'message' => 'Guide updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update guide!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Question Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $guideID = Crypt::decryptString($id);

            $guide = InterviewQuestion::findOrFail($guideID);
            $guide->delete();

            return response()->json([
                'success' => true,
                'message' => 'Guide deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete guide!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Question Destroy Multiple
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
    
            InterviewQuestion::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Guides deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete guides!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
