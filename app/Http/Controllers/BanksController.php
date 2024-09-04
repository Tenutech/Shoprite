<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Bank;
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

class BanksController extends Controller
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
    | Banks Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/banks')) {
            //Banks
            $banks = Bank::all();

            return view('admin/banks', [
                'banks' => $banks
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Bank Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Bank Create
            $bank = Bank::create([
                'name' => $request->name,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($bank->id);

            return response()->json([
                'success' => true,
                'bank' => $bank,
                'encID' => $encID,
                'message' => 'Bank created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create bank!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bank Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $bankID = Crypt::decryptString($id);

            $bank = Bank::findOrFail($bankID);

            return response()->json([
                'bank' => $bank,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get bank!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bank Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Bank ID
        $bankID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Bank
            $bank = Bank::findorfail($bankID);

            //Bank Update
            $bank->name = $request->name;
            $bank->icon = $request->icon ?: null;
            $bank->color = $request->color ?: null;
            $bank->save();

            return response()->json([
                'success' => true,
                'bank' => $bank,
                'message' => 'Bank updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bank!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bank Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $bankID = Crypt::decryptString($id);

            $bank = Bank::findOrFail($bankID);
            $bank->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bank deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete bank!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bank Destroy Multiple
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

            Bank::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Banks deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banks!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
