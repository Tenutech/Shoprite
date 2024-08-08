<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\InterviewQuestion;
use App\Models\InterviewTemplate;
use App\Models\ChatTemplate;
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

class InterviewTemplateController extends Controller
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

    public function index(Request $request)
    {
        if (view()->exists('admin/template')) {
            //Template ID
            if ($request->id) {
                $templateID = Crypt::decryptString($request->id);
            } else {
                $templateID = null;
                return view('404');
            }

            //Interview Questions
            $questions = InterviewQuestion::where('template_id', $templateID)
            ->orderBy('sort')
            ->get();

            return view('admin/template', [
                'templateID' => $templateID,
                'questions' => $questions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Question Add
    |--------------------------------------------------------------------------
    */

    public function question_store(Request $request)
    {
        //Validate
        $request->validate([
            'question' => 'required|string',
            'type' => 'required|string|in:text,number,rating,textarea',
            'sort' => 'required|int'
        ]);

        try {
            $templateID = Crypt::decryptString($request->template_id);

            //Interview Question Create
            $question = InterviewQuestion::create([
                'template_id' => $templateID,                
                'question' => $request->question,
                'type' => $request->type ?: null,
                'sort' => $request->sort ?: 0
            ]);

            $encID = Crypt::encryptString($question->id);

            return response()->json([
                'success' => true,
                'question' => $question,
                'encID' => $encID,
                'message' => 'Question created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create question!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Question Detail
    |--------------------------------------------------------------------------
    */

    public function question_details($id)
    {
        try {
            $questionID = Crypt::decryptString($id);

            $question = InterviewQuestion::findOrFail($questionID);

            return response()->json([
                'question' => $question,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get question!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Question Update
    |--------------------------------------------------------------------------
    */

    public function question_update(Request $request)
    {
        //Question ID
        $questionID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'question' => 'required|string',
            'type' => 'required|string|in:text,number,rating,textarea',
            'sort' => 'required|int'
        ]);

        try {
            //Interview Question
            $question = InterviewQuestion::findorfail($questionID);

            //Interview Question Update
            $question->question = $request->question;
            $question->type = $request->type ?: null;
            $question->sort = $request->sort ?: 0;
            $question->save();

            return response()->json([
                'success' => true,
                'question' => $question,
                'message' => 'Question updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update question!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Question Delete
    |--------------------------------------------------------------------------
    */

    public function question_destroy($id)
    {
        try {
            $questionID = Crypt::decryptString($id);

            $question = InterviewQuestion::findOrFail($questionID);
            $question->delete();

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete question!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Template Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        try {
            // Create a new template
            $template = new InterviewTemplate();
            $template->save();

            // Encrypt the ID
            $encryptedID = Crypt::encryptString($template->id);

            // Redirect to the index page with the encrypted ID
            return response()->json([
                'success' => true,
                'redirect_url' => route('template.index', ['id' => $encryptedID]),
                'message' => 'Template created successfully!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Template Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            // Decrypt the ID
            $templateID = Crypt::decryptString($id);

            // Find and delete the template
            $template = InterviewTemplate::findOrFail($templateID);
            $template->delete();

            // Reset the auto-increment value
            $maxId = InterviewTemplate::max('id') ?? 0; // Get the highest current ID or 0 if no records
            DB::statement('ALTER TABLE interview_templates AUTO_INCREMENT = '.($maxId + 1));

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template!',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
