<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Faq;
use App\Models\Query;
use App\Models\QueryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendQueryToJira;
use App\Jobs\SendQueryEmailNotification;

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
            // User ID
            $userId = Auth::id();

            // Auth User
            $user = User::findorfail($userId);

            // Role
            $role = $user->role_id;

            // General FAQs
            $generalFaqs = Faq::where('type', 'General')
                              ->where('role_id', '>=', $role)
                              ->get();

            // Account FAQs
            $accountFaqs = Faq::where('type', 'Account')->get();

            // Queries
            $queries = Query::where('user_id', Auth::id())->get();

            // Categories
            $categories = QueryCategory::get();

            return view('help', [
                'user' => $user,
                'generalFaqs' => $generalFaqs,
                'accountFaqs' => $accountFaqs,
                'queries' => $queries,
                'categories' => $categories
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
            'category' => 'required|integer|exists:query_categories,id',
            'subject' => 'required|string|max:191',
            'body' => 'required|string'
        ]);

        try {
            // Get the authenticated user
            $user = Auth::user();

            // Retrieve the selected category
            $category = QueryCategory::findOrFail($request->category);

            // Create a new query with the provided data and authenticated user's information or null
            $query = Query::create([
                'user_id' => $user->id,
                'firstname' => $user->firstname ?? null,
                'lastname' => $user->lastname ?? null,
                'email' => $user->email ?? null,
                'phone' => $user->phone ?? null,
                'subject' => $request->subject,
                'body' => $request->body,
                'category_id' => $category->id,
                'severity' => $category->severity,
                'status' => 'Pending'
            ]);

            // Chain the SendQueryEmailNotification job to run after SendQueryToJira
            SendQueryToJira::withChain([
                new SendQueryEmailNotification($query),
            ])->dispatch($query);

            // Return a successful response with the created query
            return response()->json([
                'success' => true,
                'query' => $query,
                'category' => $category,
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
