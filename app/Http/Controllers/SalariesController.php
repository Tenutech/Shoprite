<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\SalaryBenefit;
use App\Models\Position;
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

class SalariesController extends Controller
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
    | Salary & Benefits Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin/salaries')) {
            //Salary & Benefits
            $salaries = SalaryBenefit::orderBy('position_id')->get();

            //Positions
            $positions = Position::all();

            return view('admin/salaries', [
                'salaries' => $salaries,
                'positions' => $positions
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Salary & Benefit Add
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //Validate
        $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'description' => 'required|string'
        ]);

        try {
            //Salary & Benefit Create
            $salary = SalaryBenefit::create([                
                'position_id' => $request->position_id ?: null,
                'description' => $request->description ?: null,
                'icon' => $request->icon ?: null,
                'color' => $request->color ?: null
            ]);

            $encID = Crypt::encryptString($salary->id);

            return response()->json([
                'success' => true,
                'salary' => $salary,
                'encID' => $encID,
                'message' => 'Salary created successfully!',
            ], 200);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create salary!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Salary & Benefit Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $salaryID = Crypt::decryptString($id);

            $salary = SalaryBenefit::findOrFail($salaryID);

            return response()->json([
                'salary' => $salary,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get salary!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Salary & Benefit Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Salary ID
        $salaryID = Crypt::decryptString($request->field_id);

        //Validate
        $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'description' => 'required|string'
        ]);

        try {
            //Salary & Benefit
            $salary = SalaryBenefit::findorfail($salaryID);

            //Salary & Benefit Update
            $salary->position_id = $request->position_id ?: null;
            $salary->description = $request->description ?: null;
            $salary->icon = $request->icon ?: null;
            $salary->color = $request->color ?: null;
            $salary->save();

            return response()->json([
                'success' => true,
                'salary' => $salary,
                'message' => 'Salary updated successfully!'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update salary!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Salary & Benefit Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $salaryID = Crypt::decryptString($id);

            $salary = SalaryBenefit::findOrFail($salaryID);
            $salary->delete();

            return response()->json([
                'success' => true,
                'message' => 'Salary deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete salary!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Salary & Benefit Destroy Multiple
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
    
            SalaryBenefit::destroy($decryptedIds);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Salaries deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete salaries!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
