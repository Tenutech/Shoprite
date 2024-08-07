<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Position;
use App\Models\InterviewTemplate;
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
            //Positions
            $positions = Position::all();

            //Templates
            $templates = InterviewTemplate::all();

            //Interview Questions
            $guides = InterviewQuestion::all();

            return view('admin/interview-guides', [
                'positions' => $positions,
                'templates' => $templates
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Question Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $positionID = Crypt::decryptString($id);

            $position = Position::findOrFail($positionID);

            return response()->json([
                'position' => $position,
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
        //Position ID
        $positionID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'template' => 'required|integer|min:1|exists:interview_templates,id'
        ]);

        try {
            //Position
            $position = Position::findorfail($positionID);

            //Positon Update
            $position->template_id = $request->template;
            $position->save();

            //Template ID
            $templateID = Crypt::encryptString($position->template_id);

            return response()->json([
                'success' => true,
                'position' => $position,
                'templateID' => $templateID,
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
}
