<?php

namespace App\Http\Controllers;

use App\Models\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class QueryController extends Controller
{

    public function index()
    {
        try {
            $queries = Query::where('user_id', auth()->id())->get();

            return response()->json([
                'success' => true,
                'queries' => $queries,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch queries',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:191',
            'body' => 'required|string'
        ]);

        $htmlContent = $request->input('body');

        $textContent = $this->convertHtmlToTextWithDelimiters($htmlContent);

        try {            
            $query = Query::create([                
                'subject' => $request->subject,
                'body' => $textContent,
                'user_id' => auth()->id(),
            ]);

            $encID = Crypt::encryptString($query->id);

            return response()->json([
                'success' => true,
                'weighting' => $query,
                'encID' => $encID,
                'message' => 'Query created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create query',
                'error' => $e->getMessage()
            ], 400);
        }




        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = Query::create([
            'subject' => $request->subject,
            'body' => $request->body,
        ]);

        return response()->json(['success' => 'Query submitted successfully', 'query' => $query], 201);
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
}