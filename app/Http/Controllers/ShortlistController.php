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

            //Applicants
            $applicants = Applicant::with([
                'town',
                'gender',
                'race',
                'position',
                'education',
                'readLanguages',
                'speakLanguages',
                'reason',
                'duration',
                'retrenchment',
                'brand',
                'previousPosition',
                'transport',
                'disability',
                'type',
                'bank',
                'role',
                'state',
                'contracts',
                'vacanciesFilled'
            ])
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

            // Decrypt the vacancy ID if it's provided
            $vacancyID = null;
            if ($request->has('id')) {
                $vacancyID = Crypt::decryptString($request->query('id'));
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

            //Languages
            $languages = Language::get();

            //Reasons
            $reasons = Reason::get();

            //Durations
            $durations = Duration::get();

            //Retrenchments
            $retrenchments = Retrenchment::get();

            //Brands
            $brands = Brand::get();

            //Transports
            $transports = Transport::get();

            //Disabilities
            $disabilities = Disability::get();

            //Types
            $types = Type::get();

            //Banks
            $banks = Bank::get();

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

            return view('manager/shortlist', [
                'applicants' => $applicants,
                'vacancyID'  => $vacancyID,
                'vacancies'  => $vacancies,
                'shortlistedApplicants' => $shortlistedApplicants,
                'towns' => $towns,
                'genders' => $genders,
                'races' => $races,
                'positions' => $positions,
                'educations' => $educations,
                'languages' => $languages,
                'reasons' => $reasons,
                'durations' => $durations,
                'retrenchments' => $retrenchments,
                'brands' => $brands,
                'transports' => $transports,
                'disabilities' => $disabilities,
                'types' => $types,
                'applicantTypes' => $applicantTypes,
                'banks' => $banks,
                'roles' => $roles,
                'states' => $states,
                'checks' => $checks,
                'minShortlistNumber' => $minShortlistNumber,
                'maxShortlistNumber' => $maxShortlistNumber
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
                'number' => "required|integer|min:$minShortlistNumber|max:$maxShortlistNumber"
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

            //Applicants
            $query = Applicant::with([
                'town',
                'gender',
                'race',
                'position',
                'education',
                'readLanguages',
                'speakLanguages',
                'reason',
                'duration',
                'retrenchment',
                'brand',
                'previousPosition',
                'transport',
                'disability',
                'type',
                'bank',
                'role',
                'state',
                'checks',
                'latestChecks',
                'contracts',
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
            ->whereNull('shortlist_id')
            ->whereNull('appointed_id')
            ->where('no_show', '<=', 2)
            ->when($vacancyBrandID, function ($query, $vacancyBrandID) {
                // Apply the brand_id filter only if a brand_id exists
                $query->where(function ($query) use ($vacancyBrandID) {
                    $query->where('brand_id', 1)
                          ->orWhere('brand_id', $vacancyBrandID);
                });
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

            // Get the selected checks from the request
            $selectedChecks = $request->input('checks', []);

            // Execute the query and get the results
            $applicantsCollection = $query->get();

            // Perform checks and assign statuses
            $this->performChecks($applicantsCollection, $selectedChecks);

            // Map the results to include encrypted IDs and load checks relation
            $applicantsCollection = $applicantsCollection->map(function ($applicant) use ($userID) {
                $applicant->encrypted_id = Crypt::encryptString($applicant->id);
                $applicant->load('latestChecks'); // Load the 'latestChecks' relation
                return $applicant;
            });

            $applicants = $applicantsCollection->toArray();

            // Convert the Collection to an array after plucking the 'id's
            $applicantIds = $applicantsCollection->pluck('id')->toArray();

            $sap_numbers = SapNumber::where('vacancy_id', $vacancyID)->pluck('sap_number');

            // Save Shortlist
            $shortlistData = [
                'user_id' => $userID,
                'vacancy_id' => $vacancyID,
                'applicant_ids' => json_encode($applicantIds),
                'sap_numbers' => $sap_numbers,
            ];
            $shortlist = Shortlist::updateOrCreate(['user_id' => $userID, 'vacancy_id' => $vacancyID], $shortlistData);

            // Update all the applicants with the shortlist_id
            Applicant::whereIn('id', $applicantIds)->update(['shortlist_id' => $shortlist->id]);

            return response()->json([
                'success' => true,
                'message' => 'Shortlist generated!',
                'applicants' => $applicants,
                'sapNumbers' => $sap_numbers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate shortlist.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Perform Checks
    |--------------------------------------------------------------------------
    */

    protected function performChecks($applicants, $selectedChecks)
    {
        $results = ['Passed', 'Failed', 'Discrepancy']; // The possible statuses
        $reasons = [
            'Passed' => config('shortlist.reasons.passed'),
            'Failed' => config('shortlist.reasons.failed'),
            'Discrepancy' => config('shortlist.reasons.discrepancy')
        ];

        foreach ($applicants as $applicant) {
            foreach ($selectedChecks as $checkType => $checkIds) {
                foreach ($checkIds as $checkId) {
                    // Special condition for applicant ID 25 and check ID 1
                    if ($applicant->id == 1 && $checkId == 1) {
                        $randomResult = 'Failed';
                        $fileName = 'Burger_Johannes_IDVPlus_202311111054.pdf';
                    } else {
                        // Existing logic for other cases
                        $randomResult = $applicant->id == 1 ? 'Passed' : $results[array_rand($results)];
                        $fileName = null; // Reset fileName for other cases
                        if ($applicant->id == 1) {
                            switch ($checkId) {
                                case 2:
                                    $fileName = 'Burger_JohannesHendrik_CCR_202311111047.pdf';
                                    break;
                                case 3:
                                    $fileName = 'Burger_JohannesHendrik_FPS_202311111047.pdf';
                                    break;
                                case 4:
                                    $fileName = 'Burger_JohannesHendrik_DL_202311122254.pdf';
                                    break;
                                case 5:
                                    $fileName = 'Burger_JohannesHendrik_BAV_202311111047.pdf';
                                    break;
                            }
                        }
                    }
                    $dummyReason = $reasons[$randomResult]; // Get the dummy reason for the result

                    // Add check to the applicant_checks table with reason
                    $applicant->checks()->attach($checkId, [
                        'result' => $randomResult,
                        'reason' => $dummyReason,
                        'file' => $fileName // Include the file name if set
                    ]);
                }
            }
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
                    'position',
                    'education',
                    'readLanguages',
                    'speakLanguages',
                    'reason',
                    'duration',
                    'retrenchment',
                    'brand',
                    'previousPosition',
                    'transport',
                    'disability',
                    'type',
                    'bank',
                    'role',
                    'state',
                    'checks',
                    'latestChecks',
                    'contracts',
                    'contracts',
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
                ->orderBy('score', 'desc')
                ->get();

                // Encrypt the ID of each applicant
                $applicants->each(function ($applicant) {
                    $applicant->encrypted_id = Crypt::encryptString($applicant->id);
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Shortlist generated!',
                    'applicants' => $applicants,
                ]);
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to generate shortlist.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate shortlist.',
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

            //Update the Applicant
            Applicant::find($applicantIDToRemove)->update(['shortlist_id' => null]);

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
