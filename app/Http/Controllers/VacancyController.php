<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Type;
use App\Models\User;
use App\Models\Store;
use App\Models\Position;
use App\Models\Vacancy;
use App\Models\Shortlist;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\SapNumber;
use App\Models\Application;
use App\Models\Notification;
use App\Models\VacancyFill;
use App\Services\VacancyService;
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
use Illuminate\Validation\ValidationException;

class VacancyController extends Controller
{
    protected $vacancyService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(VacancyService $vacancyService)
    {
        $this->middleware(['auth', 'verified']);
        $this->vacancyService = $vacancyService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


    /**
     * Display the list of vacancies along with associated data such as positions,
     * stores, and types. If a vacancy ID is provided, it decrypts the ID, retrieves
     * the vacancy along with its related data (user, position, store, etc.), and
     * returns the vacancy view for managers.
     *
     * This method also checks if the requested view exists and falls back to a
     * 404 view if not. The positions and stores data are retrieved and filtered to
     * exclude certain IDs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     *
     * @throws \Exception
     */
    public function index(Request $request)
    {
        if (view()->exists('manager/vacancy')) {
            $userId = Auth::id();

            $user = User::findorfail($userId);

            //Store
            $store = Store::with([
                'brand',
                'town',
                'division',
                'region'
            ])
            ->where('id', $user->store_id)
            ->first();

            $vacancy = null;

            if ($request->id) {
                $vacancyId = Crypt::decryptString($request->id);

                $vacancy = Vacancy::with([
                    'user',
                    'position',
                    'store',
                    'type',
                    'status',
                    'interviews',
                    'appointed',
                    'sapNumbers',
                    'availableSapNumbers',
                    'shortlists'
                ])
                ->findOrFail($vacancyId);
            }

            //Positions logic based on user role and brand
            $positions = collect(); // Default to an empty collection

            if (in_array($user->role_id, [1, 2])) {
                // If role_id is 1 or 2, get all positions where id > 1
                $positions = Position::where('id', '>', 1)->orderBy('name')->get();
            } elseif ($user->role_id == 3) {
                // If role_id is 3, get all positions where brand_id is in the stores matching division_id
                $storeBrandIds = Store::where('division_id', $user->division_id)
                    ->pluck('brand_id'); // Get the brand_ids of all stores in the user's region

                // If $storeBrandIds contains 3 or 4 and does not contain 2, add 2
                if ($storeBrandIds->contains(3) || $storeBrandIds->contains(4)) {
                    $storeBrandIds->push(2);
                }

                // Now get all positions where brand_id is in the store's brand_ids
                $positions = Position::whereIn('brand_id', $storeBrandIds)->orderBy('name')
                    ->get();
            } elseif ($user->role_id == 4) {
                // If role_id is 4, get all positions where brand_id is in the stores matching division_id
                $storeBrandIds = Store::where('division_id', $user->division_id)
                    ->pluck('brand_id'); // Get the brand_ids of all stores in the user's division

                // If $storeBrandIds contains 3 or 4 and does not contain 2, add 2
                if ($storeBrandIds->contains(3) || $storeBrandIds->contains(4)) {
                    $storeBrandIds->push(2);
                }

                // Now get all positions where brand_id is in the store's brand_ids
                $positions = Position::whereIn('brand_id', $storeBrandIds)->orderBy('name')
                    ->get();
            } elseif ($user->role_id == 6) {
                // If role_id is 6, check if the user has a brand_id
                if ($user->brand_id) {
                    // If $user->brand_id is 2, 3, or 4, get positions where brand_id is 2
                    if (in_array($user->brand_id, [2, 3, 4])) {
                        $positions = Position::where('brand_id', 2)->orderBy('name')->get();
                    } else {
                        // Otherwise, get positions where brand_id matches the user's brand_id
                        $positions = Position::where('brand_id', $user->brand_id)->orderBy('name')->get();
                    }
                }
            }

            //Stores logic
            $stores = collect(); // Default to an empty collection

            if (in_array($user->role_id, [1, 2])) {
                // If role_id is 1 or 2, get all stores where id > 1
                $stores = Store::with(['brand', 'town', 'division', 'region'])
                    ->get();
            } elseif ($user->role_id == 3) {
                // If role_id is 3, get all stores where region_id = user->region_id
                $stores = Store::with(['brand', 'town', 'division', 'region'])
                    ->where('division_id', $user->division_id)
                    ->get();
            } elseif ($user->role_id == 4) {
                // If role_id is 4, get all stores where division_id = user->division_id
                $stores = Store::with(['brand', 'town', 'division', 'region'])
                    ->where('division_id', $user->division_id)
                    ->get();
            } elseif ($user->role_id == 6) {
                // Get stores where store_id is = user->store_id
                $stores = Store::with(['brand', 'town', 'division', 'region'])
                    ->where('id', $user->store_id)
                    ->get();
            }

            //Types
            $types = Type::get();

            return view('manager/vacancy', [
                'user' => $user,
                'store' => $store,
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
        // Authenticated user's ID
        $userID = Auth::id();

        // Find the authenticated user by their ID
        $user = User::findOrFail($userID);

        // Fetch the selected position and its associated brand information
        $position = Position::with('brand')
            ->where('id', $request->position_id)
            ->firstOrFail(); // If the position doesn't exist, fail with a 404 error

        // Fetch the selected store and its associated region and division information
        $store = Store::with(['region', 'division'])
            ->where('id', '=', $request->store_id)
            ->first(); // Fetch the store, returns null if not found

        // Extract the region ID and division ID from the store data
        $regionID = $store->region_id;
        $divisionID = $store->division_id;

        // Validate the input data from the request
        $request->validate([
            'position_id' => ['required', 'integer', 'exists:positions,id',
                function ($attribute, $value, $fail) use ($store, $position) {
                    // Custom validation: Ensure the selected position's brand_id matches the store's brand_id
                    if ($position->brand_id !== $store->brand_id) {
                        $fail('The position brand does not match the store brand.');
                    }
                }
            ],
            'open_positions' => 'required|integer|min:1|max:10', // Ensure open positions is a number between 1 and 10
            'sap_numbers' => 'required|array', // Validate that sap_numbers is an array
            'sap_numbers.*' => ['digits:8',
                function ($attribute, $value, $fail) use ($userID, $store) {
                    $sapNumber = SapNumber::with(['vacancy', 'vacancyFills'])->where('sap_number', $value)->first();

                    if ($sapNumber) {
                        if ($sapNumber->vacancy && $sapNumber->vacancy->store_id !== $store->id) {
                            $fail('The SAP number ' . $value . ' has already been taken.');
                        } elseif (!$sapNumber->vacancy || $sapNumber->vacancy->store_id === $store->id) {
                            // Handle SAP number reassignment without deleting vacancy fills
                            DB::transaction(function () use ($sapNumber) {
                                // Get all vacancy fills related to the SAP number
                                $vacancyFills = $sapNumber->vacancyFills()->get();

                                // Loop through vacancy fills to update associated applicants
                                foreach ($vacancyFills as $vacancyFill) {
                                    // Find the applicant where appointed_id = vacancyFill->id
                                    $applicant = Applicant::where('appointed_id', $vacancyFill->id)->first();

                                    if ($applicant) {
                                        // Set appointed_id and shortlist_id to null
                                        $applicant->update([
                                            'appointed_id' => null,
                                            'shortlist_id' => null
                                        ]);
                                    }
                                }

                                // Update the associated vacancy fills to set sap_number_id to null
                                $sapNumber->vacancyFills()->update([
                                    'sap_number_id' => null
                                ]);
                            });
                        }
                    }
                }
            ],
            'type_id' => ['required', 'integer', 'exists:types,id',
                function ($attribute, $value, $fail) use ($request) {
                    // Positions that require type_id = 5 (YES programme)
                    $yesPositions = [52, 54, 56];
                    // Positions that require type_id = 6 (RRP programme)
                    $rrpPositions = [53, 55, 57];

                    if ((in_array($request->position_id, $yesPositions) && $value != 5) || (!in_array($request->position_id, $yesPositions) && $value == 5)) {
                        $fail('You cannot select this job position for YES programme. Please select a YES job position.');
                    }

                    if ((in_array($request->position_id, $rrpPositions) && $value != 6) || (!in_array($request->position_id, $rrpPositions) && $value == 6)) {
                        $fail('You cannot select this job position for RRP programme. Please select a RRP job position.');
                    }
                }
            ],
            'store_id' => ['required','integer','exists:stores,id',
                function ($attribute, $value, $fail) use ($user, $regionID, $divisionID, $store) {
                    // For RPP (role_id = 3), ensure the user belongs to the same region as the store
                    if ($user->role_id == 3 && $user->division_id !== $divisionID) {
                        $fail('You are not authorized to create a vacancy at the selected store.');
                    }

                    // For DTDP (role_id = 4), ensure the user belongs to the same division as the store
                    if ($user->role_id == 4 && $user->division_id !== $divisionID) {
                        $fail('You are not authorized to create a vacancy at the selected store.');
                    }

                    // For Manager (role_id = 6), ensure the user is assigned to the same store as the one being selected
                    if ($user->role_id == 6 && $user->store_id !== (int) $store->id) {
                        $fail('You are not authorized to create a vacancy at the selected store.');
                    }
                }
            ],
        ]);

        try {
            // Begin a database transaction to ensure data integrity
            DB::beginTransaction();

            // Create the new vacancy in the database
            $vacancy = Vacancy::create([
                'user_id' => $userID, // The ID of the user creating the vacancy
                'position_id' => $request->position_id, // The selected position ID
                'open_positions' => $request->open_positions, // Number of open positions
                'filled_positions' => 0, // Initially, no positions are filled
                'store_id' => $request->store_id, // The selected store ID
                'type_id' => $request->type_id, // The selected type ID
                'status_id' => 2, // Vacancy status set to 2 (you may adjust this depending on your status definitions)
                'advertisement' => 'Any' // Placeholder advertisement value
            ]);

            // Create the SAP numbers associated with the vacancy
            foreach ($request->sap_numbers as $sap) {
                $sapNumber = SapNumber::where('sap_number', $sap)->first();

                if ($sapNumber && $sapNumber->vacancy->store_id === $store->id) {
                    // Update the SAP number to the new vacancy after creation
                    $sapNumber->vacancy_id = $vacancy->id;
                    $sapNumber->save();
                } else {
                    // Create a new SAP number for the vacancy
                    $vacancy->sapNumbers()->create([
                        'sap_number' => $sap,
                    ]);
                }
            }

            // If the vacancy was successfully created, create a notification
            if ($vacancy->wasRecentlyCreated) {
                $notification = new Notification();
                $notification->user_id = $vacancy->user_id; // User receiving the notification
                $notification->causer_id = Auth::id(); // User who triggered the notification
                $notification->subject()->associate($vacancy); // Associate the notification with the newly created vacancy
                $notification->type_id = 1; // Notification type ID (1, for example, may represent a vacancy creation)
                $notification->notification = "Created new vacancy 🔔"; // Message for the notification
                $notification->read = "No"; // Set notification status to unread
                $notification->save(); // Save the notification
            }

            // Commit the transaction (persist the changes)
            DB::commit();

            // Encrypt the vacancy ID for secure use in responses
            $encryptedID = Crypt::encryptString($vacancy->id);

            // Return a successful JSON response
            return response()->json([
                'success' => true,
                'message' => 'Vacancy created successfully!',
                'vacancy' => $vacancy, // Return the newly created vacancy
                'encrypted_id' => $encryptedID // Return the encrypted vacancy ID
            ], 201);
        } catch (ValidationException $e) {
            // Catch validation errors and return them in a structured JSON response
            return response()->json([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $e->errors() // This will return the validation errors in a key-value format
            ], 422); // 422 Unprocessable Entity is standard for validation errors
        } catch (Exception $e) {
            // Catch any other errors and rollback the transaction
            DB::rollBack();

            // Return a JSON response with the error details
            return response()->json([
                'success' => false,
                'message' => 'Failed to create vacancy: ' . $e->getMessage(),
                'error' => $e->getMessage() // Return the error message
            ], 400); // HTTP 400 Bad Request
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
        // Authenticated user's ID
        $userID = Auth::id();

        // Find the authenticated user by their ID
        $user = User::findOrFail($userID);

        // Fetch the selected position and its associated brand information
        $position = Position::with('brand')
            ->where('id', $request->position_id)
            ->firstOrFail(); // If the position doesn't exist, fail with a 404 error

        // Fetch the selected store and its associated region and division information
        $store = Store::with(['region', 'division'])
            ->where('id', '=', $request->store_id)
            ->first(); // Fetch the store, returns null if not found

        // Extract the region ID and division ID from the store data
        $regionID = $store->region_id;
        $divisionID = $store->division_id;

        // Validate the input data from the request
        $request->validate([
            'position_id' => ['required', 'integer', 'exists:positions,id',
                function ($attribute, $value, $fail) use ($store, $position) {
                    // Custom validation: Ensure the selected position's brand_id matches the store's brand_id
                    if ($position->brand_id !== $store->brand_id) {
                        $fail('The position brand does not match the store brand.');
                    }
                }
            ],
            'open_positions' => 'required|integer|min:1|max:10', // Ensure open positions is a number between 1 and 10
            'sap_numbers' => 'required|array', // Validate that sap_numbers is an array
            'sap_numbers.*' => ['digits:8',
                function ($attribute, $value, $fail) use ($userID) {
                    $sapNumber = SapNumber::with(['vacancy', 'vacancyFills'])->where('sap_number', $value)->first();

                    if ($sapNumber) {
                        if ($sapNumber->vacancy->user_id !== $userID && $sapNumber->vacancy->deleted == 'No') {
                            $fail('The SAP number ' . $value . ' has already been taken.');
                        } elseif ($sapNumber->vacancy->user_id === $userID) {
                            // Handle SAP number reassignment without deleting vacancy fills
                            DB::transaction(function () use ($sapNumber) {
                                // Get all vacancy fills related to the SAP number
                                $vacancyFills = $sapNumber->vacancyFills()->get();

                                // Loop through vacancy fills to update associated applicants
                                foreach ($vacancyFills as $vacancyFill) {
                                    // Find the applicant where appointed_id = vacancyFill->id
                                    $applicant = Applicant::where('appointed_id', $vacancyFill->id)->first();

                                    if ($applicant) {
                                        // Set appointed_id and shortlist_id to null
                                        $applicant->update([
                                            'appointed_id' => null,
                                            'shortlist_id' => null
                                        ]);
                                    }
                                }

                                // Update the associated vacancy fills to set sap_number_id to null
                                $sapNumber->vacancyFills()->update([
                                    'sap_number_id' => null
                                ]);
                            });
                        }
                    }
                }
            ],
            'type_id' => 'required|integer|exists:types,id', // Ensure the type_id exists in the types table
            'store_id' => ['required','integer','exists:stores,id',
                function ($attribute, $value, $fail) use ($user, $regionID, $divisionID, $store) {
                    // For RPP (role_id = 3), ensure the user belongs to the same region as the store
                    if ($user->role_id == 3 && $user->division_id !== $divisionID) {
                        $fail('You are not authorized to create a vacancy at the selected store.');
                    }

                    // For DTDP (role_id = 4), ensure the user belongs to the same division as the store
                    if ($user->role_id == 4 && $user->division_id !== $divisionID) {
                        $fail('You are not authorized to create a vacancy at the selected store.');
                    }

                    // For Manager (role_id = 6), ensure the user is assigned to the same store as the one being selected
                    if ($user->role_id == 6 && $user->store_id !== (int) $store->id) {
                        $fail('You are not authorized to create a vacancy at the selected store.');
                    }
                }
            ],
        ]);

        try {
            // Start a database transaction to ensure all actions happen together or not at all
            DB::beginTransaction();

            // Find and decrypt the vacancy ID
            $vacancy = Vacancy::findOrFail(Crypt::decryptString($request->id));

            // Update the vacancy with the new data
            $vacancy->update([
                'position_id' => $request->position_id, // Update the position ID
                'open_positions' => $request->open_positions, // Update the number of open positions
                'store_id' => $request->store_id, // Update the store ID
                'type_id' => $request->type_id, // Update the type ID
                'status_id' => 2, // Update the status (assuming 2 means "active" or "open")
                'advertisement' => 'Any' // Placeholder value for advertisement
            ]);

            // Get the existing SAP numbers for the vacancy
            $existingSapNumbers = $vacancy->sapNumbers;

            // Convert the request SAP numbers to an array with their index as the key
            $newSapNumbers = array_values($request->sap_numbers);

            // Iterate over the existing SAP numbers and update them if they exist
            foreach ($existingSapNumbers as $index => $sapNumber) {
                if (isset($newSapNumbers[$index])) {
                    // Update the SAP number if it exists in the new SAP numbers array
                    $sapNumber->update(['sap_number' => $newSapNumbers[$index]]);
                    unset($newSapNumbers[$index]); // Remove the updated SAP number from the new SAP numbers array
                } else {
                    // Delete the SAP number if it is no longer needed
                    $sapNumber->delete();
                }
            }

            // Add any new SAP numbers that weren't in the original set
            foreach ($newSapNumbers as $sapNumber) {
                $vacancy->sapNumbers()->create(['sap_number' => $sapNumber]);
            }

            // Commit the transaction to save all the changes
            DB::commit();

            // Encrypt the updated vacancy ID for secure transmission
            $encryptedID = Crypt::encryptString($vacancy->id);

            // Return a successful response with the updated vacancy and its encrypted ID
            return response()->json([
                'success' => true,
                'message' => 'Vacancy updated successfully!',
                'vacancy' => $vacancy,
                'encrypted_id' => $encryptedID
            ], 201); // 201 Created status
        } catch (ValidationException $e) {
            // Catch validation errors and return them in a structured response
            return response()->json([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $e->errors() // This will return the validation errors in a key-value format
            ], 422); // 422 Unprocessable Entity is standard for validation errors
        } catch (Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollBack();

            // Return an error response with the exception message
            return response()->json([
                'success' => false,
                'message' => 'Failed to update vacancy: ' . $e->getMessage(),
                'error' => $e->getMessage() // Return the error message
            ], 400); // 400 Bad Request status
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
            // Authenticated user's ID
            $userID = Auth::id();

            // Find the authenticated user by their ID
            $user = User::findOrFail($userID);

            // Ensure the user has the necessary role permission
            if ($user->role_id > 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You do not have permission to delete this vacancy.'
                ], 403);
            }

            // Decrypt the provided ID
            $vacancyId = Crypt::decryptString($id);

            // Find the vacancy model
            $vacancy = Vacancy::findOrFail($vacancyId);

            if (!$vacancy->shortlists->isEmpty() && !is_null($vacancy->shortlists->first()?->applicant_ids) && !empty(json_decode($vacancy->shortlists->first()?->applicant_ids, true))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacancy cannot be deleted as it has an active shortlist.'
                ], 400);
            }

            if (!$vacancy->interviews->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacancy cannot be deleted as it has scheduled interviews.'
                ], 400);
            }

            if (!$vacancy->appointed->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacancy cannot be deleted as it has active appointments.'
                ], 400);
            }

            // Start a transaction to ensure all related data is removed properly
            DB::beginTransaction();

            // Delete the vacancy
            $vacancy->deleted = 'Yes';
            $vacancy->save();

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

    /**
     * Fill the specified vacancy with selected applicants.
     *
     * This method decrypts the vacancy ID, validates the selected applicants, and then fills the
     * vacancy by updating the vacancy's open and filled positions. It ensures that only valid
     * applicants who have been interviewed are considered. If the vacancy has open positions
     * available, the method proceeds to appoint the selected applicants, create notifications,
     * send WhatsApp messages, and update applicant data. If all positions are filled, the remaining
     * applicants are notified of their rejection.
     *
     * @param  \Illuminate\Http\Request  $request  The request object containing vacancy and applicant data.
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function vacancyFill(Request $request)
    {
        try {
            // Decrypt the encrypted vacancy_id and sap_number from the request
            $vacancyId = Crypt::decryptString($request->input('vacancy_id'));
            $sapNumberId = Crypt::decryptString($request->input('sap_number'));

            // Merge decrypted IDs back into the request for validation purposes
            $request->merge([
                'vacancy_id_decrypted' => $vacancyId,
                'sap_number_id_decrypted' => $sapNumberId,
            ]);

            // Validate the request data
            $request->validate([
                'applicants_vacancy' => 'required|array',
                'applicants_vacancy.*' => 'exists:applicants,id',
                'vacancy_id_decrypted' => 'required|exists:vacancies,id',
                'sap_number_id_decrypted' => 'required|exists:sap_numbers,id',
            ]);

            // Retrieve the selected applicants from the request
            $selectedApplicants = $request->input('applicants_vacancy');

            // Begin database transaction to ensure data integrity
            DB::beginTransaction();

            // Retrieve the vacancy along with its related data
            $vacancy = Vacancy::with([
                'position',
                'store.brand',
                'store.town',
                'applicants'
            ])
            ->find($vacancyId);

            // If the vacancy doesn't exist, return an error response
            if (!$vacancy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacancy not found'
                ], 400);
            }

            // Check if there are open positions available in the vacancy
            if ($vacancy->open_positions == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No open positions available'
                ], 400);
            }

            // Count the number of selected applicants
            $numSelectedApplicants = count($selectedApplicants);

            // Ensure that the number of selected applicants does not exceed available positions
            if ($vacancy->open_positions < $numSelectedApplicants) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only ' . $vacancy->open_positions . ' positions available'
                ], 400);
            }

            // Retrieve the SapNumber instance using the decrypted ID
            $sapNumber = SapNumber::find($sapNumberId);

            // Double-check that the SAP number exists
            if (!$sapNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'SAP Number not found'
                ], 400);
            }

            // Loop through each selected applicant to process their appointment
            foreach ($selectedApplicants as $applicantId) {
                // Retrieve the applicant by ID
                $applicant = Applicant::find($applicantId);

                // If applicant does not exist, skip to the next iteration
                if (!$applicant) {
                    continue;
                }

                // Check if the applicant has already been appointed to the vacancy
                $alreadyAppointed = VacancyFill::where('vacancy_id', $vacancy->id)
                    ->where('applicant_id', $applicantId)
                    ->exists();

                if ($alreadyAppointed) {
                    // Return an error response if the applicant is already appointed
                    return response()->json([
                        'success' => false,
                        'message' => "Applicant {$applicant->firstname} {$applicant->lastname} has already been appointed to this vacancy."
                    ], 400);
                }

                // Check if the applicant has been interviewed
                $applicantWithScore = $this->applicantsWithScore($vacancyId, $applicantId);

                if (!$applicantWithScore || empty($applicantWithScore)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Applicant {$applicant->firstname} {$applicant->lastname} has not been interviewed yet."
                    ], 400);
                }

                // Retrieve the application associated with the applicant and vacancy
                $application = Application::where('user_id', $applicant->id)
                    ->where('vacancy_id', $vacancy->id)
                    ->first();

                // Update application approval status to 'Yes' if application exists
                if ($application) {
                    $application->approved = 'Yes';
                    $application->save();
                }

                // Retrieve the latest interview for the applicant where vacancy_id = $vacancyId and interviewer_id = Auth::id()
                $latestInterview = Interview::where('applicant_id', $applicant->id)
                    ->where('vacancy_id', $vacancy->id)
                    ->where('interviewer_id', Auth::id())
                    ->orderBy('updated_at', 'desc') // Fetch the latest interview
                    ->first();

                // Check if the applicant has not already been appointed to the vacancy
                if (!$vacancy->appointed->contains($applicantId)) {
                    // Create a new VacancyFill record to associate applicant with vacancy and SAP number
                    $vacancyFill = VacancyFill::create([
                        'vacancy_id'    => $vacancy->id,
                        'applicant_id'  => $applicantId,
                        'sap_number_id' => $sapNumber->id,
                        'sap_number'    => $sapNumber->sap_number,
                        'approved'      => 'Yes'
                    ]);

                    // Update the applicant's appointed_id to reference the new VacancyFill record
                    $applicant->appointed_id = $vacancyFill->id;

                    // Ensure appointments field is an array, then append the new vacancy_id
                    $appointments = $applicant->appointments ? json_decode($applicant->appointments, true) : [];
                    $appointments[] = $vacancy->id;

                    // Remove duplicates and update the field
                    $applicant->appointments = json_encode(array_unique($appointments));

                    // Update employment field based on vacancy type
                    if ($vacancy->type->id == 3) {
                        $applicant->employment = 'F';
                    } elseif ($vacancy->type->id == 4) {
                        $applicant->employment = 'S';
                    } elseif ($vacancy->type->id == 5) {
                        $applicant->employment = 'Y';
                    } elseif ($vacancy->type->id == 6) {
                        $applicant->employment = 'R';
                    }

                    $applicant->save();

                    // Update the interview status to 'Appointed'
                    if ($latestInterview) {
                        $latestInterview->status = 'Appointed';
                        $latestInterview->save();
                    }

                    // Retrieve the user associated with the applicant
                    $userId = $applicant->user ? $applicant->user->id : null;

                    // If user exists, create a notification about the appointment
                    if ($userId) {
                        $notification = new Notification();
                        $notification->user_id = $userId;
                        $notification->causer_id = Auth::id();
                        $notification->subject()->associate($vacancyFill);
                        $notification->type_id = 1;
                        $notification->notification = "You have been Appointed 🎉";
                        $notification->read = "No";
                        $notification->save();
                    }

                    // Dispatch a job to update the applicant's monthly data
                    UpdateApplicantData::dispatch($applicant->id, 'updated', 'Appointed', $vacancyId)->onQueue('default');

                    // Prepare a congratulatory WhatsApp message for the applicant
                    $whatsappMessage = "Congratulations " . $applicant->firstname ?: 'N/A' . "! You have been appointed for the position of " .
                        optional($vacancy->position)->name ?: 'N/A' . " at " .
                        optional($vacancy->store->brand)->name ?: 'N/A' . " (" .
                        optional($vacancy->store->town)->name ?: 'N/A' . "). " .
                        "We are excited to have you join our team!";

                    // Define the message type
                    $type = 'template';

                    // Define the template
                    $template = 'appointed_2';

                    // Prepare the variables (you can define these as per your needs)
                    $variables = [
                        $applicant->firstname ?: 'N/A',  // If $applicant->firstname is null, use 'N/A'
                        optional($vacancy->position)->name ?: 'N/A',  // If $vacancy->position or its name is null, use 'N/A'
                        optional($vacancy->store->brand)->name ?: 'N/A',  // If $vacancy->store->brand or its name is null, use 'N/A'
                        optional($vacancy->store)->name ?: 'N/A',  // If $vacancy->store or its name is null, use 'N/A'
                        optional($vacancy->store->town)->name ?: 'N/A'  // If $vacancy->store->town or its name is null, use 'N/A'
                    ];

                    // Dispatch a job to send the WhatsApp message
                    SendWhatsAppMessage::dispatch($applicant, $whatsappMessage, $type, $template, $variables);

                    // Delete records from the pivot table for the specific applicant and the filtered users
                    DB::table('applicant_save')
                      ->where('applicant_id', $applicant->id)
                      ->delete();
                }
            }

            // Update vacancy's filled and open positions
            $vacancy->filled_positions += $numSelectedApplicants;
            $vacancy->open_positions = max($vacancy->open_positions - $numSelectedApplicants, 0); // Ensure it doesn't go below zero
            $vacancy->save();

            // If no open positions remain after appointments, notify unappointed applicants
            if ($vacancy->open_positions == 0) {
                // Retrieve the shortlist for the vacancy
                $shortlist = Shortlist::where('vacancy_id', $vacancyId)->first();

                if ($shortlist) {
                    // Decode applicant_ids if it's a JSON string or unserialize if it's serialized
                    $applicantIds = is_array($shortlist->applicant_ids)
                        ? $shortlist->applicant_ids
                        : json_decode($shortlist->applicant_ids, true); // Adjust if using serialized data with unserialize()

                    // Get the appointed IDs from the vacancy
                    $appointedApplicantIds = $vacancy->appointed->pluck('id')->toArray();

                    // Ensure the $selectedApplicants doesn't contain duplicates from appointed IDs
                    $filteredSelectedApplicants = array_diff($selectedApplicants, $appointedApplicantIds);

                    // Merge appointed and filtered selected applicants
                    $combinedApplicantIds = array_merge($appointedApplicantIds, $filteredSelectedApplicants);

                    // Ensure we have an array before filtering
                    if (is_array($applicantIds)) {
                        // Filter out the applicant IDs who were not appointed or selected
                        $removedApplicantIds = array_diff($applicantIds, $combinedApplicantIds); // Get the removed applicants

                        // Update `shortlist_id` to null for removed applicants
                        Applicant::whereIn('id', $removedApplicantIds)->update(['shortlist_id' => null]);

                        // Filter out the applicant IDs who were not appointed or selected
                        $updatedApplicantIds = array_filter($applicantIds, function ($applicantId) use ($combinedApplicantIds) {
                            // Keep the applicant if they are in the combined list (appointed or selected)
                            return in_array($applicantId, $combinedApplicantIds);
                        });

                        // Merge appointed and updated applicant IDs (again ensuring no duplicates)
                        $updatedApplicantIds = array_unique(array_merge($appointedApplicantIds, $updatedApplicantIds));

                        // Reindex the array to reset keys
                        $updatedApplicantIds = array_values($updatedApplicantIds);

                        // Convert back to JSON or serialized if needed, then update the shortlist
                        $shortlist->applicant_ids = json_encode($updatedApplicantIds); // or serialize() if serialized
                        $shortlist->save();
                    }
                }

                // Combine all applicants associated with the vacancy with interviewed applicants
                $allApplicants = $vacancy->applicants->merge($vacancy->interviews->pluck('applicant'));

                // Loop through all applicants associated with the vacancy
                foreach ($allApplicants as $applicant) {
                    // Skip applicants who have been appointed
                    if (in_array($applicant->id, $selectedApplicants) || $applicant->appointed_id) {
                        continue;
                    }

                    // Retrieve the application associated with the applicant and vacancy
                    $application = Application::where('user_id', $applicant->id)
                        ->where('vacancy_id', $vacancy->id)
                        ->first();

                    // Check if the applicant has been interviewed (interview object is available)
                    $interview = Interview::where('applicant_id', $applicant->id)
                        ->where('vacancy_id', $vacancy->id)
                        ->where('interviewer_id', Auth::id())
                        ->whereIn('status', ['Scheduled', 'Reschedule', 'Confirmed', 'Completed'])
                        ->first();

                    // If application exists, update its approval status to 'No'
                    if ($application) {
                        $application->approved = 'No';
                        $application->save();

                        // Only send a notification if the application status was changed
                        if ($application->wasChanged()) {
                            // Check if the applicant has a user and the user exists in the users table
                            if ($applicant->user && User::where('id', $applicant->user->id)->exists()) {
                                $notification = new Notification();
                                $notification->user_id = $applicant->user->id;
                                $notification->causer_id = Auth::id();
                                // Associate notification with the application
                                $notification->subject()->associate($application);
                                $notification->type_id = 1;
                                $notification->notification = "Has been declined 🚫";
                                $notification->read = "No";
                                $notification->save();

                                // Dispatch a job to update the applicant's monthly data as 'Rejected'
                                UpdateApplicantData::dispatch($applicant->id, 'updated', 'Rejected', $vacancyId)->onQueue('default');
                            }
                        }
                    } elseif ($interview) {
                        // Update the interview status to 'Regretted' only if the current status is not 'Appointed'
                        if ($interview->status !== 'Appointed') {
                            $interview->status = 'Regretted';
                            $interview->save();
                        }

                        // If no application but an interview exists, create a notification associated with the interview
                        // Check if the applicant has a user and the user exists in the users table
                        if ($applicant->user && User::where('id', $applicant->user->id)->exists()) {
                            $notification = new Notification();
                            $notification->user_id = $applicant->user->id;
                            $notification->causer_id = Auth::id();
                            // Associate notification with the interview
                            $notification->subject()->associate($interview);
                            $notification->type_id = 1;
                            $notification->notification = "Has been declined 🚫";
                            $notification->read = "No";
                            $notification->save();

                            // Set the applicant's shortlist_id to null
                            $applicant->shortlist_id = null;
                            $applicant->save();

                            // Dispatch a job to update the applicant's monthly data as 'Rejected'
                            UpdateApplicantData::dispatch($applicant->id, 'updated', 'Rejected', $vacancyId)->onQueue('default');
                        }

                        // Prepare a congratulatory WhatsApp message for the applicant
                        $whatsappMessage = "Dear " . $applicant->firstname ?: 'N/A' . ", thank you for your interest in the " .
                            optional($vacancy->position)->name ?: 'N/A' . " position at " .
                            optional($vacancy->store->brand)->name ?: 'N/A' . " (" .
                            optional($vacancy->store->town)->name ?: 'N/A' . "). We truly appreciate the time and effort you invested in your application.
                            After careful consideration, we regret to inform you that we have selected another candidate for this role. Please know that this
                            decision does not diminish the value of your skills and experience.
                            We encourage you to apply for future opportunities with us, and we wish you all the best in your job search and career journey.";

                        // Define the message type
                        $type = 'template';

                        // Define the template
                        $template = 'regretted_2';

                        // Prepare the variables (you can define these as per your needs)
                        $variables = [
                            $applicant->firstname ?: 'N/A',  // If $applicant->firstname is null, use 'N/A'
                            optional($vacancy->position)->name ?: 'N/A',  // If $vacancy->position or its name is null, use 'N/A'
                            optional($vacancy->store->brand)->name ?: 'N/A',  // If $vacancy->store->brand or its name is null, use 'N/A'
                            optional($vacancy->store)->name ?: 'N/A',  // If $vacancy->store or its name is null, use 'N/A'
                            optional($vacancy->store->town)->name ?: 'N/A'  // If $vacancy->store->town or its name is null, use 'N/A'
                        ];

                        // Dispatch a job to send the WhatsApp message
                        SendWhatsAppMessage::dispatch($applicant, $whatsappMessage, $type, $template, $variables);
                    }
                }
            }

            // Commit the database transaction after all operations are successful
            DB::commit();

            // Map the availableSapNumbers to include encrypted IDs
            $availableSapNumbers = SapNumber::where('vacancy_id', $vacancy->id)
                ->whereDoesntHave('vacancyFills')
                ->get()
                ->map(function ($sapNumber) {
                    return [
                        'id' => Crypt::encryptString($sapNumber->id),
                        'sap_number' => $sapNumber->sap_number
                    ];
                });

            // Return a success response with the updated vacancy data
            return response()->json([
                'success' => true,
                'message' => 'Vacancy filled!',
                'vacancy' => [
                    'vacancy' => $vacancy,
                    'available_sap_numbers' => $availableSapNumbers
                ]
            ], 200);
        } catch (\Exception $e) {
            // Rollback the transaction in case of any exception to maintain data integrity
            DB::rollBack();

            // Return an error response with exception message
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
     * @param  array  $selectedApplicantsIds
     * @return \Illuminate\Http\JsonResponse
     */
    public function applicantsWithScore($vacancyId, $applicantId)
    {
        // Fetch the applicant's interview data
        $interview = Interview::where('vacancy_id', $vacancyId)
            ->where('applicant_id', $applicantId)
            ->first();

        // Return the score or null if no interview found
        return $interview ? $interview->score : null;
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
