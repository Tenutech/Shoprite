<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Faq;
use App\Models\Query;
use App\Models\QueryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
        $request->validate([
            'category' => 'required|integer|exists:query_categories,id',
            'subject' => 'required|string|max:191',
            'body' => 'required|string', // Body contains HTML with embedded images
        ]);

        try {
            $user = Auth::user();
            $category = QueryCategory::findOrFail($request->category);

            // Ensure the directory exists
            $queriesPath = public_path('images');
            if (!File::exists($queriesPath)) {
                File::makeDirectory($queriesPath, 0755, true);
            }

            // Extract and process images embedded in the body
            $body = $request->body;
            $imageUrls = [];

            if (preg_match_all('/<img[^>]+src="data:image\/[^;]+;base64,([^"]+)"/', $body, $matches)) {
                foreach ($matches[1] as $index => $base64Image) {
                    $imageData = base64_decode($base64Image);
            
                    // Generate a unique filename
                    $imageName = uniqid('query_image_') . '.png';
            
                    // Save the image in the storage/app/public/images folder
                    $relativePath = 'images/' . $imageName; // Relative path for storage
                    $storagePath = storage_path('app/public/' . $relativePath);
                    file_put_contents($storagePath, $imageData);
            
                    // Generate the URL to access the stored file
                    $imageUrl = asset('storage/' . $relativePath);
                    $imageUrls[] = $imageUrl; // Save URLs for reference
            
                    // Replace the base64 image in the body with the public URL
                    $body = str_replace($matches[0][$index], '<img src="' . $imageUrl . '"', $body);
                }
            }

            // Create a new query
            $query = Query::create([
                'user_id' => $user->id,
                'firstname' => $user->firstname ?? null,
                'lastname' => $user->lastname ?? null,
                'email' => $user->email ?? null,
                'phone' => $user->phone ?? null,
                'subject' => $request->subject,
                'body' => $body,
                'category_id' => $category->id,
                'severity' => $category->severity,
                'status' => 'Pending',
            ]);

            // Save the image URLs to the query_images table
            foreach ($imageUrls as $url) {
                $query->images()->create(['url' => $url]);
            }

            // Dispatch jobs for notifications
            SendQueryToJira::withChain([
                new SendQueryEmailNotification($query),
            ])->dispatch($query);

            return response()->json([
                'success' => true,
                'query' => $query->load('images'),
                'category' => $category,
                'message' => 'Query created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create query',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
