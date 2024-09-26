<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Role;
use App\Models\Faq;
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

class FAQsController extends Controller
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
    | FAQ Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/faqs')) {
            //FAQs
            $faqs = Faq::all();

            //Roles
            $roles = Role::all();

            return view('admin/faqs', [
                'faqs' => $faqs,
                'roles' => $roles
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | FAQ Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'faq_name' => 'required|string|max:191',
            'description' => 'required|string',
            'role_id' => 'nullable|integer|exists:roles,id',
            'type' => 'required|string|in:Account,General'
        ]);

        try {
            //FAQ Create
            $faq = Faq::create([
                'name' => $request->faq_name,
                'description' => $request->description,
                'role_id' => $request->role_id,
                'type' => $request->type
            ]);

            $encID = Crypt::encryptString($faq->id);

            return response()->json([
                'success' => true,
                'faq' => $faq,
                'encID' => $encID,
                'message' => 'FAQ template created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create faq template!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FAQ Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $faqID = Crypt::decryptString($id);

            $faq = Faq::findOrFail($faqID);

            return response()->json([
                'faq' => $faq,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get faq template!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FAQ Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //FAQ ID
        $faqID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'faq_name' => 'required|string|max:191',
            'description' => 'required|string',
            'role_id' => 'nullable|integer|exists:roles,id',
            'type' => 'required|string|in:Account,General'
        ]);

        try {
            //FAQ Template
            $faq = Faq::findorfail($faqID);

            //FAQ Update
            $faq->name = $request->faq_name;
            $faq->description = $request->description;
            $faq->role_id = $request->role_id;
            $faq->type = $request->type;
            $faq->save();

            return response()->json([
                'success' => true,
                'message' => 'FAQ template updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update faq template!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FAQ Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $faqID = Crypt::decryptString($id);

            $faq = Faq::findOrFail($faqID);
            $faq->delete();

            return response()->json([
                'success' => true,
                'message' => 'FAQ template deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete FAQ template!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FAQ Destroy Multiple
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
            $decryptedIds = array_map(function ($id) {
                return Crypt::decryptString($id);
            }, $ids);

            DB::beginTransaction();

            Faq::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'FAQ templates deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete faq templates!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
