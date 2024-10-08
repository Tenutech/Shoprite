<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Role;
use App\Models\Race;
use App\Models\Type;
use App\Models\Town;
use App\Models\Bank;
use App\Models\Chat;
use App\Models\Check;
use App\Models\Brand;
use App\Models\Gender;
use App\Models\Reason;
use App\Models\Vacancy;
use App\Models\Position;
use App\Models\Duration;
use App\Models\Language;
use App\Models\Setting;
use App\Models\Shortlist;
use App\Models\State;
use App\Models\SapNumber;
use App\Models\Applicant;
use App\Models\Education;
use App\Models\Transport;
use App\Models\Disability;
use App\Models\Retrenchment;
use App\Models\ApplicantType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;

class ShortlistController extends Controller
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
    | Shortlist Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        if (view()->exists('manager/shortlist')) {
            // Auth User ID
            $userID = Auth::id();

            //Auth User
            $user = User::find($userID);

            // Decrypt the vacancy ID if it's provided
            $vacancyID = null;
            $vacancy = null;

            if ($request->has('id')) {
                $vacancyID = Crypt::decryptString($request->query('id'));
                $vacancy = Vacancy::find($vacancyID);
            }

            //Vacancies
            $vacancies = Vacancy::with([
                'position',
                'store.brand',
                'store.town'
            ])
            ->where('user_id', $userID)
            ->get();

            //Shortlist
            $shortlist = Shortlist::where('user_id', $userID)->where('vacancy_id', $vacancyID)->first();
            $shortlistedApplicants = $shortlist ? json_decode($shortlist->applicant_ids, true) : [];

            //Towns
            $towns = Town::get();

            //Genders
            $genders = Gender::get();

            //Races
            $races = Race::get();

            //Positions
            $positions = Position::get();

            //Education Levels
            $educations = Education::get();

            //Durations
            $durations = Duration::get();

            //Brands
            $brands = Brand::get();

            //Applicant Types
            $applicantTypes = ApplicantType::get();

            //Roles
            $roles = Role::get();

            //States
            $states = State::orderBy('sort')->get();

            //Checks
            $checks = Check::get();

            //Shortlist Limits
            $minShortlistNumber = Setting::where('key', 'min_shorlist_number')->first()->value ?? 5;
            $maxShortlistNumber = Setting::where('key', 'max_shorlist_number')->first()->value ?? 20;
            $maxDistanceFromStore = Setting::where('key', 'max_distance_from_store')->first()->value ?? 50;

            return view('manager/shortlist', [
                'user'  => $user,
                'vacancyID'  => $vacancyID,
                'vacancy'  => $vacancy,
                'vacancies'  => $vacancies,
                'shortlistedApplicants' => $shortlistedApplicants,
                'towns' => $towns,
                'genders' => $genders,
                'races' => $races,
                'positions' => $positions,
                'educations' => $educations,
                'durations' => $durations,
                'brands' => $brands,
                'applicantTypes' => $applicantTypes,
                'roles' => $roles,
                'states' => $states,
                'checks' => $checks,
                'minShortlistNumber' => $minShortlistNumber,
                'maxShortlistNumber' => $maxShortlistNumber,
                'maxDistanceFromStore' => $maxDistanceFromStore
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Applicants (Generate Shortlist)
    |--------------------------------------------------------------------------
    */

    public function applicants(Request $request)
    {
        try {
            // Check if the user is authenticated
            if (!Auth::check()) {
                return redirect()->route('logout');  // Redirect to the logout route
            }

            // Decrypt Vacancy ID with error handling
            try {
                // Vacancy ID
                $vacancyID = Crypt::decryptString($request->input('vacancy_id'));
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Payload',
                    'error' => $e->getMessage()
                ], 400);
            }

            // Merge the decrypted vacancy ID into the request
            $request->merge(['vacancy_id_decrypted' => $vacancyID]);

            // Fetch the Vacancy model using the decrypted ID
            $vacancy = Vacancy::findOrFail($vacancyID);

            // Get the min and max shortlist numbers from the settings, with default values
            $minShortlistNumber = Setting::where('key', 'min_shorlist_number')->first()->value ?? 5;
            $maxShortlistNumber = Setting::where('key', 'max_shorlist_number')->first()->value ?? 20;

            // Validation rules
            $validatedData = $request->validate([
                'vacancy_id_decrypted' => 'required|integer|exists:vacancies,id',
                'number' => "required|integer|min:$minShortlistNumber|max:$maxShortlistNumber",
                'filters.coordinates' => [
                    'required',
                    'regex:/\d+km from: \(-?\d+\.\d+, -?\d+\.\d+\)/', // Custom regex to validate the coordinates format
                ]
            ], [
                'filters.coordinates.required' => 'A location filter is required. Please refresh the page.',
                'filters.coordinates.regex' => 'The location filter must be in the correct format: {radius}km from: (latitude, longitude).'
            ]);

            // Auth User ID
            $userID = Auth::id();

            // Check for existing shortlist and update applicants if needed
            $existingShortlist = Shortlist::where('user_id', $userID)->where('vacancy_id', $vacancyID)->first();

            if ($existingShortlist) {
                // Identify applicants to be updated
                $applicantsToUpdate = Applicant::where('shortlist_id', $existingShortlist->id)
                                                ->whereNull('appointed_id')
                                                ->get();

                // Set shortlist_id to null for applicants with non-null appointed_id
                foreach ($applicantsToUpdate as $applicant) {
                    $applicant->update(['shortlist_id' => null]);
                }
            }

            // Use optional() to safely access the vacancy's store brand_id
            $vacancyBrandID = optional($vacancy->store)->brand_id;

            // Get state ID where code = complete
            $completeStateID = State::where('code', 'complete')->value('id');

            //Applicants
            $query = Applicant::with([
                'town',
                'gender',
                'race',
                'education',
                'duration',
                'brands',
                'role',
                'state',
                'latestInterviewWithScore',
                'vacanciesFilled' => function ($query) use ($vacancyID) {
                    $query->where('vacancy_id', $vacancyID);
                },
                'interviews' => function ($query) use ($vacancyID) {
                    $query->where('vacancy_id', $vacancyID);
                },
                'savedBy' => function ($query) use ($userID) {
                    $query->where('user_id', $userID);
                }
            ])
            ->where(function ($query) {
                $query->where('employment', '!=', 'B')
                      ->orWhereNull('employment'); // Add condition to include NULL employment
            })
            ->whereNull('shortlist_id')
            ->whereNull('appointed_id')
            ->where('no_show', '<=', 2)
            ->where(function ($query) {
                $query->whereNull('user_delete')   // Include where user_delete is NULL
                      ->orWhere('user_delete', 'No'); // Or where user_delete equals 'No'
            })
            ->where('state_id', '>=', $completeStateID)            
            ->whereHas('brands', function ($query) use ($vacancyBrandID) {
                // Check if the applicant has the vacancyBrandID in their brands or brand_id = 1
                $query->where('brand_id', 1);

                if ($vacancyBrandID) {
                    $query->orWhere('brand_id', $vacancyBrandID);
                }
            })
            ->orderBy('score', 'desc');

            // Check if shortlist_type_id is 1 and vacancy_id is provided
            if ($request->input('shortlist_type_id') == '2' && $request->has('vacancy_id')) {
                // Filter applicants based on the associated user's approved applications for the specific vacancy
                $query->whereHas('user.appliedVacancies', function ($subQuery) use ($vacancyID) {
                    $subQuery->where('vacancy_id', $vacancyID) // 'vacancies.id' changed to 'vacancy_id' because we are inside the pivot table context
                            ->where('applications.approved', 'Yes'); // Use 'applications.approved' to specify the pivot table and column
                });
            }

            // Check if shortlist_type_id is 4 then only get from saved applicants
            if ($request->input('shortlist_type_id') == '4') {
                $query->whereHas('savedBy', function ($query) use ($userID) {
                    $query->where('user_id', $userID);
                });
            }

            // Apply filters if they are present in the request
            if ($request->has('filters')) {
                $filters = $request->input('filters');
                foreach ($filters as $key => $values) {
                    // Skip the applicant_type_id filter if its value is 3
                    if ($key === 'applicant_type_id' && ($values == '3' || empty($values))) {
                        continue;
                    }

                    // Skip the coordinates filter here, handle it separately
                    if ($key === 'coordinates') {
                        continue;
                    }

                    if (is_array($values)) {
                        // If the filter is an array, use whereIn to filter for any of the values
                        $query->whereIn($key, $values);
                    } else {
                        // Otherwise, filter by the single value
                        $query->where($key, $values);
                    }
                }
            }

            // Apply location filter if it is present in the request
            if ($request->has('filters.coordinates')) {
                $coordinatesFilter = $request->input('filters.coordinates');
                // Extract the radius and coordinates from the filter
                preg_match('/(\d+)km from: \((-?\d+\.\d+),\s*(-?\d+\.\d+)\)/', $coordinatesFilter, $matches);
                if ($matches) {
                    $radius = (int) $matches[1];
                    $latitude = (float) $matches[2];
                    $longitude = (float) $matches[3];

                    // Split the 'coordinates' field into latitude and longitude
                    $query->whereRaw("ST_Distance_Sphere(point(SUBSTRING_INDEX(coordinates, ',', -1), SUBSTRING_INDEX(coordinates, ',', 1)), point(?, ?)) <= ?", [
                        $longitude, $latitude, $radius * 1000 // radius in meters
                    ]);
                }
            }

            // Apply the number limit if it is present in the request
            if ($request->has('number')) {
                $number = (int) $request->input('number');
                $query->take($number);
            }

             // Fetch the filtered applicants based on the query
            $filteredApplicants = $query->get();

            // Fetch the filtered applicants based on the query
            $filteredApplicants = $query->get();

            // Now fetch the applicants with scheduled or rescheduled interviews
            $interviewedApplicants = Applicant::with([
                'town',
                'gender',
                'race',
                'education',
                'duration',
                'brands',
                'role',
                'state',
                'latestInterviewWithScore',
                'vacanciesFilled' => function ($query) use ($vacancyID) {
                    $query->where('vacancy_id', $vacancyID);
                },
                'interviews' => function ($query) use ($vacancyID) {
                    $query->where('vacancy_id', $vacancyID);
                },
                'savedBy' => function ($query) use ($userID) {
                    $query->where('user_id', $userID);
                }
            ])->whereHas('interviews', function ($interviewQuery) use ($vacancyID) {
                $interviewQuery->where('vacancy_id', $vacancyID)
                            ->whereIn('status', ['Scheduled', 'Rescheduled']);
            })->get();

            // Merge the interviewed applicants with the filtered applicants**
            $mergedApplicants = $interviewedApplicants->merge($filteredApplicants);

            // Remove duplicate applicants (just in case)
            $applicantsCollection = $mergedApplicants->unique('id');

            // Now, calculate the distance for each applicant and map it as a distance attribute
            $applicantsCollection = $applicantsCollection->map(function ($applicant) use ($latitude, $longitude) {
                // Extract the applicant's coordinates (assumed in 'latitude,longitude' format)
                if (isset($applicant->coordinates) && strpos($applicant->coordinates, ',') !== false) {
                    list($applicantLat, $applicantLng) = explode(',', $applicant->coordinates);

                    // Convert strings to floats
                    $applicantLat = (float) $applicantLat;
                    $applicantLng = (float) $applicantLng;

                    // Check if latitude, longitude, applicantLat, or applicantLng is missing
                    if (!is_numeric($latitude) || !is_numeric($longitude) || !is_numeric($applicantLat) || !is_numeric($applicantLng)) {
                        $applicant->distance = 'N/A';  // Set distance to 'N/A' if any coordinate is missing
                    } else {
                        // Calculate the distance using the Haversine formula
                        $earthRadius = 6371;  // Earth's radius in kilometers
                        $dLat = deg2rad($latitude - $applicantLat);
                        $dLng = deg2rad($longitude - $applicantLng);

                        $a = sin($dLat / 2) * sin($dLat / 2) +
                            cos(deg2rad($latitude)) * cos(deg2rad($applicantLat)) *
                            sin($dLng / 2) * sin($dLng / 2);

                        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                        $distance = $earthRadius * $c;  // Distance in kilometers

                        // Round the distance to 2 decimal places and add it as an attribute
                        $applicant->distance = round($distance, 2) . ' km';
                    }
                } else {
                    $applicant->distance = 'N/A';  // Set to 'N/A' if coordinates are missing or improperly formatted
                }

                return $applicant;
            });

            // Map the results to include encrypted IDs and load checks relation
            $applicantsCollection = $applicantsCollection->map(function ($applicant) use ($userID) {
                $applicant->encrypted_id = Crypt::encryptString($applicant->id);
                return $applicant;
            });

            //Aplicants Array
            $applicants = $applicantsCollection->toArray();

            // Convert the Collection to an array after plucking the 'id's
            $applicantIds = $applicantsCollection->pluck('id')->toArray();

            // Count of applicants
            $applicantCount = $applicantsCollection->count();

            //SAP Numbers
            $sapNumbers = SapNumber::where('vacancy_id', $vacancyID)->pluck('sap_number');

            // Save Shortlist
            $shortlistData = [
                'user_id' => $userID,
                'vacancy_id' => $vacancyID,
                'applicant_ids' => json_encode($applicantIds),
                'sap_numbers' => $sapNumbers,
            ];

            //Update or create the shortlist
            $shortlist = Shortlist::updateOrCreate(['user_id' => $userID, 'vacancy_id' => $vacancyID], $shortlistData);

            // Update all the applicants with the shortlist_id
            Applicant::whereIn('id', $applicantIds)->update(['shortlist_id' => $shortlist->id]);

            return response()->json([
                'success' => true,
                'message' => 'Shortlist generated!',
                'applicants' => $applicants,
                'applicantCount' => $applicantCount,
                'sapNumbers' => $sapNumbers,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return the validation errors
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->errors(), // Return validation errors
            ], 422);
        } catch (\Exception $e) {
            // Return other errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate shortlist.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Shortlist Applicants
    |--------------------------------------------------------------------------
    */

    public function shortlistApplicants(Request $request)
    {
        try {
            // Auth User ID
            $userID = Auth::id();

            // Decrypt Vacancy ID with error handling
            try {
                // Vacancy ID
                $vacancyID = Crypt::decryptString($request->input('vacancy_id'));
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Payload',
                    'error' => $e->getMessage()
                ], 400);
            }

            //Vavancy
            $vacancy = Vacancy::findorfail($vacancyID);

            // Check if store exists
            if (!$vacancy->store || !$vacancy->store->coordinates) {
                return response()->json([
                    'success' => false,
                    'message' => 'Store not found!'
                ], 400);
            }

            // Extract the store's coordinates
            $storeCoordinates = explode(', ', $vacancy->store->coordinates);
            if (count($storeCoordinates) !== 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid store coordinates!'
                ], 400);
            }
            $storeLat = (float) $storeCoordinates[0];
            $storeLng = (float) $storeCoordinates[1];

            //Shortlist
            $shortlist = Shortlist::where('user_id', $userID)
                                  ->where('vacancy_id', $vacancyID)
                                  ->first();

            if ($shortlist) {
                $applicantIDs = json_decode($shortlist->applicant_ids);
                $applicants = Applicant::with([
                    'town',
                    'gender',
                    'race',
                    'education',
                    'duration',
                    'brands',
                    'role',
                    'state',
                    'latestInterviewWithScore',
                    'vacanciesFilled' => function ($query) use ($vacancyID) {
                        $query->where('vacancy_id', $vacancyID);
                    },
                    'interviews' => function ($query) use ($vacancyID) {
                        $query->where('vacancy_id', $vacancyID);
                    },
                    'savedBy' => function ($query) use ($userID) {
                        $query->where('user_id', $userID);
                    }
                ])
                ->whereIn('id', $applicantIDs)
                ->orderByRaw('FIELD(id, ' . implode(',', $applicantIDs) . ')')
                ->get();

                // Calculate the distance for each applicant
                $applicants->each(function ($applicant) use ($storeLat, $storeLng) {
                    // Check if the applicant's coordinates are valid
                    if (isset($applicant->coordinates) && strpos($applicant->coordinates, ',') !== false) {
                        list($applicantLat, $applicantLng) = explode(',', $applicant->coordinates);

                        $applicantLat = (float) $applicantLat;
                        $applicantLng = (float) $applicantLng;

                        // Check if latitude, longitude, applicantLat, or applicantLng is missing
                        if (!is_numeric($storeLat) || !is_numeric($storeLng) || !is_numeric($applicantLat) || !is_numeric($applicantLng)) {
                            $applicant->distance = 'N/A';  // Set distance to 'N/A' if any coordinate is missing
                        } else {
                            // Calculate the distance using the Haversine formula
                            $earthRadius = 6371;  // Earth's radius in kilometers
                            $dLat = deg2rad($storeLat - $applicantLat);
                            $dLng = deg2rad($storeLng - $applicantLng);

                            $a = sin($dLat / 2) * sin($dLat / 2) +
                                cos(deg2rad($storeLat)) * cos(deg2rad($applicantLat)) *
                                sin($dLng / 2) * sin($dLng / 2);

                            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                            $distance = $earthRadius * $c;  // Distance in kilometers

                            // Round the distance to 2 decimal places and add it as an attribute
                            $applicant->distance = round($distance, 2) . ' km';
                        }
                    } else {
                        $applicant->distance = 'N/A';  // Set to 'N/A' if coordinates are missing or improperly formatted
                    }

                    // Encrypt the applicant's ID
                    $applicant->encrypted_id = Crypt::encryptString($applicant->id);
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Shortlist loaded!',
                    'applicants' => $applicants,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load shortlist.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load shortlist.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update Shortlist
    |--------------------------------------------------------------------------
    */

    public function shortlistUpdate(Request $request)
    {
        try {
            // Decrypt Vacancy ID with error handling
            try {
                // Vacancy ID
                $vacancyID = Crypt::decryptString($request->input('vacancy_id'));
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Payload',
                    'error' => $e->getMessage()
                ], 400);
            }

            $userID = Auth::id(); // Or however you obtain the authenticated user's ID
            $applicantIDToRemove = $request->input('applicant_id');

            // Fetch the applicant
            $applicant = Applicant::with([
                'interviews',
            ])
            ->findorfail($applicantIDToRemove);

            // Check if the applicant has been appointed
            if ($applicant->appointed_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Applicant has have been appointed.',
                ], 400);
            }

            // Check if the applicant has interviews scheduled for this vacancy
            $interviews = $applicant->interviews()->where('vacancy_id', $vacancyID)
                ->whereIn('status', ['Schedule', 'Reschedule', 'Confirmed'])
                ->exists();

            if ($interviews) {
                return response()->json([
                    'success' => false,
                    'message' => $applicant->firstname . ' cannot be removed, they have an interview scheduled.',
                ], 400);
            }

            // Fetch the shortlist
            $shortlist = Shortlist::where('user_id', $userID)->where('vacancy_id', $vacancyID)->firstOrFail();

            // Remove the applicant ID from the list
            $applicantIDs = collect(json_decode($shortlist->applicant_ids));
            $updatedApplicantIDs = $applicantIDs->reject(function ($id) use ($applicantIDToRemove) {
                return $id == $applicantIDToRemove;
            })->values(); // values() to reset the keys

            // Update the shortlist
            $shortlist->applicant_ids = $updatedApplicantIDs->toJson();
            $shortlist->save();

            // Update the Applicant to set shortlist_id to null
            $applicant->update(['shortlist_id' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Applicant removed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shortlist.',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
