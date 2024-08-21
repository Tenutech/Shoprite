<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Type;
use App\Models\User;
use App\Models\Store;
use App\Models\Position;
use App\Models\Vacancy;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\SapNumber;
use App\Models\Application;
use App\Models\Notification;
use App\Models\VacancyFill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use App\Jobs\SendWhatsAppMessage;
use App\Jobs\UpdateApplicantData;

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
            $userId = Auth::id();

            $user = User::findorfail($userId);

            $vacancy = null;

            if ($request->id) {
                $vacancyId = Crypt::decryptString($request->id);

                $vacancy = Vacancy::with([
                    'user', 
                    'position', 
                    'store', 
                    'type', 
                    'status'
                ])
                ->findOrFail($vacancyId);
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
                'user' => $user,
                'vacancy' => $vacancy,
                'positions' => $positions,
                'stores' => $stores,
                'types' => $types
            ]);
        }
        return view('404');
    }

    /**
     * Store a newly created vacancy and its associated SAP numbers.
     *
     * This method validates the incoming request data, creates a new vacancy,
     * and optionally associates SAP numbers with the newly created vacancy.
     * It also creates a notification for the user when a new vacancy is created.
     * All operations are wrapped in a database transaction to ensure data integrity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function store(Request $request)
    {        
        //Validate Input           
        $request->validate([
            'position_id' => 'required|integer',
            'open_positions' => 'required|integer',
            'sap_numbers' => 'required|array',
            'store_id' => 'required|integer',
            'type_id' => 'required|integer',
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
                'status_id' => 2,
                'advertisement' => 'Any'
            ]);

            // Create SAP Numbers        
            foreach ($request->sap_numbers as $sap) {
                $vacancy->sapNumbers()->create([
                    'sap_number' => $sap,
                ]);
            }
            
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

    /**
     * Update the specified vacancy and sync its associated SAP numbers.
     *
     * This method updates the vacancy details and synchronizes the associated
     * SAP numbers with the provided data. SAP numbers that are not in the
     * request will be removed, and new ones will be added. The function also
     * logs the update activity and handles transaction management.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {           
        $request->validate([
            'position_id' => 'required|integer',
            'open_positions' => 'required|integer',
            'sap_numbers' => 'required|array',
            'store_id' => 'required|integer',
            'type_id' => 'required|integer',          
        ]);

        try {
            $userID = Auth::id();

            DB::beginTransaction();

            // Find and decrypt the vacancy
            $vacancy = Vacancy::findOrFail(Crypt::decryptString($request->id));
            $vacancy->update([
                'position_id' => $request->position_id,
                'open_positions' => $request->open_positions,
                'store_id' => $request->store_id,
                'type_id' => $request->type_id,
                'status_id' => 2,
                'advertisement' => 'Any'
            ]);

            // Get the existing SAP numbers for the vacancy
            $existingSapNumbers = $vacancy->sapNumbers;

            // Convert the request SAP numbers to an associative array with their index as the key
            $newSapNumbers = array_values($request->sap_numbers);

            // Iterate over the existing SAP numbers and update them
            foreach ($existingSapNumbers as $index => $sapNumber) {
                if (isset($newSapNumbers[$index])) {
                    $sapNumber->update(['sap_number' => $newSapNumbers[$index]]);
                    unset($newSapNumbers[$index]); // Remove this from the new SAP numbers array
                } else {
                    // Remove SAP numbers that are no longer needed
                    $sapNumber->delete();
                }
            }

            // Add any new SAP numbers that weren't in the original set
            foreach ($newSapNumbers as $sapNumber) {
                $vacancy->sapNumbers()->create(['sap_number' => $sapNumber]);
            }

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

    /**
     * Remove the specified vacancy and its associated SAP numbers from storage.
     *
     * This method decrypts the given ID to find the vacancy, deletes the associated SAP numbers,
     * and then deletes the vacancy itself. If any error occurs during the process, it catches the 
     * exception and returns a corresponding error response.
     *
     * @param  string  $id  The encrypted ID of the vacancy to be deleted.
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function destroy($id)
    {
        try {
            // Decrypt the provided ID
            $vacancyId = Crypt::decryptString($id);

            // Find the vacancy model
            $vacancy = Vacancy::findOrFail($vacancyId);

            // Start a transaction to ensure all related data is removed properly
            DB::beginTransaction();

            // Delete associated SAP numbers
            $vacancy->sapNumbers()->delete();

            // Delete the vacancy
            $vacancy->delete();

            // Commit the transaction
            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Vacancy and associated SAP numbers deleted successfully!'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Vacancy not found', 
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
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
            $vacancyId = Crypt::decryptString($request->input('vacancy_id'));
            $selectedApplicants = $request->input('applicants_vacancy');
           
            // Extract user IDs from the selected applicants
            $selectedUserIds = User::whereIn('applicant_id', $selectedApplicants)->pluck('id')->filter()->toArray(); 
            $applicantsWithScore = $this->applicantsWithScore($vacancyId, $selectedUserIds);
           
            if (count($applicantsWithScore) <= 0 || empty($applicantsWithScore)) {
                return response()->json([
                    'success' => false, 
                    'message' => "Some applicants do not have an interview score"
                ], 400);
            }
           
            DB::beginTransaction();

            // Find the vacancy
            $vacancy = Vacancy::with([
                'position', 
                'store.brand', 
                'store.town',
                'applicants' 
            ])->find($vacancyId);

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

                        //Update applicant appointed_id
                        if ($applicant) {
                            $applicant->appointed_id = $vacancyFill->id;
                            $applicant->save();
                        }

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
                        UpdateApplicantData::dispatch($applicant->id, 'updated', 'Appointed', $vacancyId)->onQueue('default');

                        // Constructing the WhatsApp message
                        $whatsappMessage = "Congratulations {$applicant->firstname}! You have been appointed for the position of " . 
                        "{$vacancy->position->name} at " . 
                        "{$vacancy->store->brand->name} ({$vacancy->store->town->name}). " .
                        "We are excited to have you join our team!";

                        $type = 'text';

                        // Dispatch WhatsApp message
                        SendWhatsAppMessage::dispatch($applicant, $whatsappMessage, $type);
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
                                UpdateApplicantData::dispatch($applicant->id, 'updated', 'Rejected', $vacancyId)->onQueue('default');
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

    /**
     * Check and return the name and ID of applicants without a score for a given vacancy.
     *
     * @param  int  $vacancyId
     * @param  array  $selectedUserIds
     * @return \Illuminate\Http\JsonResponse
     */
    public function applicantsWithScore($vacancyId, array $selectedUserIds)
    {
        // Fetch the applicants without a score
        $applicantsWithScore = Interview::where('vacancy_id', $vacancyId)
            ->whereIn('applicant_id', array_values($selectedUserIds))
            ->with('applicant:id,firstname,score')
            ->get()
            ->map(function ($interview) {
                return [
                    'id' => $interview->applicant->id,
                    'firstname' => $interview->applicant->firstname,
                    'score' => $interview->score ?? ''
                ];
            })
            ->keyBy('id')
            ->toArray();
   
        $result = [];
     
        if(empty($applicantsWithScore)) {
            return $result;
        }

        foreach ($selectedUserIds as $userId) {
            if(isset($applicantsWithScore[$userId])) {
                $result[$userId]['firstname'] = $applicantsWithScore[$userId]['firstname'];
                $result[$userId]['score']  = $applicantsWithScore[$userId]['score'] ?? '';
            } 
        }

        return $result;
    }

     /* Create SAP numbers for the given vacancy ID.
     *
     * @param  int  $vacancyId
     * @param  array  $sapNumbersData
     * @return void
     */
    protected function createSapNumbers(int $vacancyId, array $sapNumbersData)
    {
        foreach ($sapNumbersData as $sapNumberData) {
            SapNumber::create([
                'vacancy_id' => $vacancyId,
                'sap_number' => $sapNumberData,
            ]);
        }
    }
}