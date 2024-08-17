<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Role;
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

class RolesController extends Controller
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
    | Roles Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/roles')) {
            //Roles
            $roles = Role::all();

            return view('admin/roles', [
                'roles' => $roles
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Role Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Role Create
            $role = Role::create([
                'name' => $request->name,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($role->id);

            return response()->json([
                'success' => true,
                'role' => $role,
                'encID' => $encID,
                'message' => 'Role created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Role Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $roleID = Crypt::decryptString($id);

            $role = Role::findOrFail($roleID);

            return response()->json([
                'role' => $role,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get role!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Role Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Role ID
        $roleID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'name' => 'required|string|max:191'
        ]);

        try {
            //Role
            $role = Role::findorfail($roleID);

            //Role Update
            $role->name = $request->name;
            $role->icon = $request->icon ?: null;
            $role->color = $request->color ?: null;
            $role->save();

            return response()->json([
                'success' => true,
                'role' => $role,
                'message' => 'Role updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Role Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $roleID = Crypt::decryptString($id);

            $role = Role::findOrFail($roleID);
            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Role Destroy Multiple
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

            Role::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Roles deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete roles!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
