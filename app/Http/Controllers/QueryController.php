<?php

namespace App\Http\Controllers;

use App\Models\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendQueryToJira;


class QueryController extends Controller
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
    | Query Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('help')) {
            // Queries
            $queries = Query::where('user_id', Auth::id())->get();
            
            return view('help', [
                'queries' => $queries
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'subject' => 'required|string|max:191',
            'body' => 'required|string'
        ]);

        try {
            // Get the authenticated user
            $user = Auth::user();

            // Create a new query with the provided data and authenticated user's information or null
            $query = Query::create([
                'user_id' => $user->id,
                'firstname' => $user->firstname ?? null,
                'lastname' => $user->lastname ?? null,
                'email' => $user->email ?? null,
                'phone' => $user->phone ?? null,
                'subject' => $request->subject,
                'body' => $request->body,
                'status' => 'Pending'
            ]);

            // Dispatch the job to send the query to Jira
            SendQueryToJira::dispatch($query);

            // Return a successful response with the created query
            return response()->json([
                'success' => true,
                'query' => $query,
                'message' => 'Query created successfully!',
            ], 200);
        } catch (Exception $e) {
            // Return an error response if the query creation fails
            return response()->json([
                'success' => false,
                'message' => 'Failed to create query',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}