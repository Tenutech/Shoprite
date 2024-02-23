<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Type;
use App\Models\Store;
use App\Models\Vacancy;
use App\Models\Position;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\VacancyFill;
use App\Models\Notification;
use App\Jobs\SendWhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class VacancyController extends Controller
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
    | Vacancies Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        if (view()->exists('manager/vacancy')) {
            $vacancy = null;

            if ($request->id) {
                $vacancyID = Crypt::decryptString($request->id);

                $vacancy = Vacancy::with([
                    'user', 
                    'position', 
                    'store', 
                    'type', 
                    'status'
                ])
                ->findOrFail($vacancyID);
            }

            //Positions
            $positions = Position::whereNotIn('id', [1, 10])->get();

            //Stores
            $stores = Store::with([
                'brand', 
                'town',
            ])
            ->get();

            //Types
            $types = Type::whereNotIn('id', [6])->get();

            return view('manager/vacancy',[
                'vacancy' => $vacancy,
                'positions' => $positions,
                'stores' => $stores,
                'types' => $types
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Vacancy Create
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {        
        //Validate Input           
        $request->validate([
            'position_id' => 'required|integer',
            'open_positions' => 'required|integer',
            'store_id' => 'required|integer',
            'type_id' => 'required|integer'
        ]);

        try {
            //User ID
            $userID = Auth::id();

            DB::beginTransaction();

            // Vacancy Create
            $vacancy = Vacancy::create([
                'user_id' => $userID,        
                'position_id' => $request->position_id,
                'open_positions' => $request->open_positions,
                'filled_positions' => 0,
                'store_id' => $request->store_id,
                'type_id' => $request->type_id,
                'status_id' => 1
            ]);

            // If a new vacancy was created, then create a notification
            if ($vacancy->wasRecentlyCreated) {
                // Create Notification
                $notification = new Notification();
                $notification->user_id = $vacancy->user_id;
                $notification->causer_id = Auth::id();
                $notification->subject()->associate($vacancy);
                $notification->type_id = 1;
                $notification->notification = "Created new vacancy 🔔";
                $notification->read = "No";
                $notification->save();
            }

            DB::commit();

            $encryptedID = Crypt::encryptString($vacancy->id);

            return response()->json([
                'success' => true,
                'message' => 'Vacancy created successfully!',
                'vacancy' => $vacancy,
                'encrypted_id' => $encryptedID
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create vacancy!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Vacancy Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Validate Input           
        $request->validate([
            'position_id' => 'required|integer',
            'open_positions' => 'required|integer',
            'store_id' => 'required|integer',
            'type_id' => 'required|integer'
        ]);

        try {            
            //User ID
            $userID = Auth::id();

            DB::beginTransaction();

            $vacancy = Vacancy::findOrFail(Crypt::decryptString($request->id));
            $vacancy->position_id = $request->position_id;
            $vacancy->open_positions = $request->open_positions;
            $vacancy->store_id = $request->store_id;
            $vacancy->type_id = $request->type_id;
            $vacancy->status_id = 1;
            $vacancy->save();

            DB::commit();

            $encryptedID = Crypt::encryptString($vacancy->id);

            return response()->json([
                'success' => true,
                'message' => 'Vacancy updated successfully!',
                'vacancy' => $vacancy,
                'encrypted_id' => $encryptedID
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update vacancy!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Vacancy Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $vacancyID = Crypt::decryptString($id);

            //Delete Vacancy
            Vacancy::destroy($vacancyID);

            return response()->json([
                'success' => true, 
                'message' => 'Vacancy deleted!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Vacancy deletion failed', 
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Vacancy Fill
    |--------------------------------------------------------------------------
    */

    public function vacancyFill(Request $request)
    {
        try {
            $vacancyID = Crypt::decryptString($request->input('vacancy_id'));
            $selectedApplicants = $request->input('applicants_vacancy');

            // Extract user IDs from the selected applicants
            $selectedUserIds = User::whereIn('applicant_id', $selectedApplicants)->pluck('id')->filter()->toArray();

            DB::beginTransaction();

            // Find the vacancy
            $vacancy = Vacancy::with([
                'position', 
                'store.brand', 
                'store.town',
                'applicants' 
            ])->find($vacancyID);

            if (!$vacancy) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Vacancy not found'
                ], 400);
            }

            if ($vacancy->open_positions == 0) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No open positiions available'
                ], 400);
            }

            $numSelectedApplicants = count($selectedApplicants);

            if ($vacancy->open_positions < $numSelectedApplicants) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Only '. $vacancy->open_positions .' positiions available'
                ], 400);
            }

            if ($vacancy->open_positions !== 0) {
                // Update open_positions and filled_positions
                $vacancy->filled_positions = $vacancy->filled_positions + $numSelectedApplicants;
                $vacancy->open_positions = max($vacancy->open_positions - $numSelectedApplicants, 0); // Ensure it doesn't go below 0
                $vacancy->save();

                // Attach applicants to the vacancy without duplicating
                foreach ($selectedApplicants as $applicantId) {
                    $applicant = Applicant::find($applicantId);

                    $application = Application::where('user_id', $applicant->id)
                                            ->where('vacancy_id', $vacancy->id)
                                            ->first();

                    if ($application) {
                        $application->approved = 'Yes';
                        $application->save();
                    }

                    if (!$vacancy->appointed->contains($applicantId)) {
                        $vacancyFill = VacancyFill::create([
                            'vacancy_id' => $vacancy->id,
                            'applicant_id' => $applicantId,
                            'approved' => 'Pending'
                        ]);

                        // Get user ID if the applicant has a user, else null
                        $userId = $applicant->user ? $applicant->user->id : null;

                        if($userId) {
                            // Create Notification
                            $notification = new Notification();
                            $notification->user_id = $userId;
                            $notification->causer_id = Auth::id();
                            $notification->subject()->associate($vacancyFill); // Assuming you want to associate the notification with the vacancy
                            $notification->type_id = 1;
                            $notification->notification = "You have been Appointed 🎉";
                            $notification->read = "No";
                            $notification->save();
                        }

                        //Update Applicant Monthly Data
                        UpdateApplicantData::dispatch($applicant->id, 'updated', 'Appointed')->onQueue('default');

                        // Constructing the WhatsApp message
                        $whatsappMessage = "Congratulations {$applicant->firstname}! You have been appointed for the position of " . 
                        "{$vacancy->position->name} at " . 
                        "{$vacancy->store->brand->name} ({$vacancy->store->town->name}). " .
                        "We are excited to have you join our team!";

                        // Dispatch WhatsApp message
                        SendWhatsAppMessage::dispatch($applicant, $whatsappMessage);
                    }
                }

                if ($vacancy->open_positions == 0) {
                    //Create notification for all applicants that where not appointed
                    foreach ($vacancy->applicants as $applicant) {
                        if (!in_array($applicant->id, $selectedUserIds)) {
                            $application = Application::where('user_id', $applicant->id)
                                                    ->where('vacancy_id', $vacancy->id)
                                                    ->first();
                    
                            if ($application) {
                                $application->approved = 'No';
                                $application->save();
                    
                                if ($application->wasChanged()) {
                                    // Create Notification
                                    $notification = new Notification();
                                    $notification->user_id = $applicant->id;
                                    $notification->causer_id = Auth::id();
                                    $notification->subject()->associate($application); // Associate notification with the application
                                    $notification->type_id = 1;
                                    $notification->notification = "Has been declined 🚫";
                                    $notification->read = "No";
                                    $notification->save();
                                }

                                //Update Applicant Monthly Data
                                UpdateApplicantData::dispatch($applicant->id, 'updated', 'Rejected')->onQueue('default');
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Vacancy filled!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false, 
                'message' => 'Failed to fill vacancy', 
                'error' => $e->getMessage()
            ], 400);
        }
    }
}