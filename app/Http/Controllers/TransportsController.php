<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Transport;
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

class TransportsController extends Controller
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
    | Transports Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/transports')) {
            //Transports
            $transports = Transport::all();

            return view('admin/transports', [
                'transports' => $transports
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Transport Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Transport Create
            $transport = Transport::create([
                'name' => $request->name,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($transport->id);

            return response()->json([
                'success' => true,
                'transport' => $transport,
                'encID' => $encID,
                'message' => 'Transport created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create transport!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Transport Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $transportID = Crypt::decryptString($id);

            $transport = Transport::findOrFail($transportID);

            return response()->json([
                'transport' => $transport,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get transport!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Transport Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Transport ID
        $transportID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Transport
            $transport = Transport::findorfail($transportID);

            //Transport Update
            $transport->name = $request->name;
            $transport->icon = $request->icon ?: null;
            $transport->color = $request->color ?: null;
            $transport->save();

            return response()->json([
                'success' => true,
                'transport' => $transport,
                'message' => 'Transport updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update transport!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Transport Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $transportID = Crypt::decryptString($id);

            $transport = Transport::findOrFail($transportID);
            $transport->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transport deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transport!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Transport Destroy Multiple
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

            Transport::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transports deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transports!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
