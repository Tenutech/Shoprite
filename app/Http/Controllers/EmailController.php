<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\EmailTemplate;
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
use App\Exports\EmailExport;
use Maatwebsite\Excel\Facades\Excel;


class EmailController extends Controller
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
    | Email Template Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/email')) {
            //Email Templates
            $emails = EmailTemplate::all();

            return view('admin/email', [
                'emails' => $emails
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Email Template Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'email_name' => 'required|string|max:191',
            'subject' => 'required|string|max:191',
            'intro' => 'required|string'
        ]);

        // Get the 'intro' field from the request
        $htmlContent = $request->input('intro');

        // Convert HTML to text with ;; delimiters
        $textContent = $this->convertHtmlToTextWithDelimiters($htmlContent);

        try {            
            //Email Template Create
            $email = EmailTemplate::create([                
                'name' => $request->email_name,
                'subject' => $request->subject,
                'intro' => $textContent
            ]);

            $encID = Crypt::encryptString($email->id);

            return response()->json([
                'success' => true,
                'weighting' => $email,
                'encID' => $encID,
                'message' => 'Email template created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create email template!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Email Template Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $emailID = Crypt::decryptString($id);

            $email = EmailTemplate::findOrFail($emailID);

            return response()->json([
                'email' => $email,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Email Template Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Email Template ID
        $emailID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'email_name' => 'required|string|max:191',
            'subject' => 'required|string|max:191',
            'intro' => 'required|string'
        ]);

        // Get the 'intro' field from the request
        $htmlContent = $request->input('intro');

        // Convert HTML to text with ;; delimiters
        $textContent = $this->convertHtmlToTextWithDelimiters($htmlContent);

        try {
            //Email Template
            $email = EmailTemplate::findorfail($emailID);

            //Email Template Update
            $email->name = $request->email_name;
            $email->subject = $request->subject;
            $email->intro = $textContent;
            $email->save();

            return response()->json([
                'success' => true,
                'message' => 'Email template updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update email template!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Email Template Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $emailID = Crypt::decryptString($id);

            $email = EmailTemplate::findOrFail($emailID);
            $email->delete();

            return response()->json([
                'success' => true,
                'message' => 'Email template deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Email template!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Email Template Destroy Multiple
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
    
            EmailTemplate::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Email templates deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete email templates!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Convert Html To Text With Delimiters
    |--------------------------------------------------------------------------
    */

    protected function convertHtmlToTextWithDelimiters($html)
    {
        // Trim to ensure there's no leading or trailing whitespace
        $html = trim($html);

        // Remove the first <p> tag and the last </p> tag
        $html = preg_replace('/^<p[^>]*>/', '', $html, 1);
        $html = preg_replace('/<\/p>$/', '', $html, 1);

        // Normalize line breaks to a single format, assuming mixed use of <br> tags and \n
        $normalizedBreaks = preg_replace(['/\<br(\s*)?\/?\>/i', "/\r\n|\r|\n/"], "\n", $html);

        // Split the content into lines
        $lines = explode("\n", $normalizedBreaks);

        // Append ';;' to each non-empty line, add an extra newline for separation, and process empty lines
        $processedLines = array_map(function($line) {
            // For non-empty lines, trim the line, append ';;', and add a newline character for separation
            // For empty lines, just return an empty string (which maintains existing empty lines without adding extra ';;')
            return trim($line) === '' ? "\n" : trim($line) . ';;' . "\n";
        }, $lines);

        // Combine all lines back together
        $text = implode("", $processedLines);

        // Ensure ';;' is appended at the end of the content if not already present
        if(substr($text, -2) !== ";;") {
            $text .= ';;';
        }

        return $text;
    }

    /*
    |--------------------------------------------------------------------------
    | Email Export
    |--------------------------------------------------------------------------
    */

    public function export()
    {
        return Excel::download(new EmailExport, 'Email Templates.xlsx');
    }
}
