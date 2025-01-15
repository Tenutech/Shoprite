<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Race;
use App\Models\Brand;
use App\Models\State;
use App\Models\Gender;
use App\Models\Shortlist;
use App\Models\Document;
use App\Models\Duration;
use App\Models\Education;
use App\Models\Applicant;
use App\Models\VacancyFill;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\ScoreWeighting;
use Illuminate\Support\Facades\DB;
use App\Services\GoogleMapsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\UpdateApplicantRequest;
use Illuminate\Validation\Rule;
use App\Jobs\ProcessUserIdNumber;
use App\Jobs\SendIdNumberToSap;
use Illuminate\Support\Facades\Log;

class ApplicantsTableController extends Controller
{
    private GoogleMapsService $googleMapsService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(GoogleMapsService $googleMapsService)
    {
        $this->middleware(['auth', 'verified']);
        $this->googleMapsService = $googleMapsService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('admin.applicants-table')) {
            // Applicants
            $applicants = Applicant::with([
                'town',
                'gender',
                'race',
                'education',
                'duration',
                'brands',
                'state',
            ])
            ->orderby('firstname')
            ->orderby('lastname')
            ->take(10)
            ->get();

            // Genders
            $genders = Gender::all();

            // Durations
            $durations = Duration::get();

            // Education
            $educations = Education::where('id', '!=', 3)->get();

            // Brand
            $brands = Brand::whereIn('id', [1, 2, 5, 6])->get();

            // Races
            $races = Race::get();

            // States
            $states = State::get();

            return view('admin/applicants-table', [
                'applicants' => $applicants,
                'genders' => $genders,
                'durations' => $durations,
                'educations' => $educations,
                'brands' => $brands,
                'races' => $races,
                'states' => $states
            ]);
        }
        return view('404');
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant Detail
    |--------------------------------------------------------------------------
    */

    public function details($id)
    {
        try {
            $applicantID = Crypt::decryptString($id);

            $applicant = applicant::with([
                'town',
                'gender',
                'race',
                'education',
                'duration',
                'brands',
                'state',
                'latestInterview'
            ])->findOrFail($applicantID);

            $latitude = '';
            $longitude = '';

            // Check if coordinates exist and split them by ','
            if (!empty($applicant->coordinates)) {
                $coordinates = explode(',', $applicant->coordinates);
                if (count($coordinates) === 2) {
                    $latitude = trim($coordinates[0]);
                    $longitude = trim($coordinates[1]);
                }
            }

            return response()->json([
                'applicant' => $applicant,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'encID' => $id
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Application Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        //Applicant ID
        $applicantId = Crypt::decryptString($request->field_id);

        //Applicant
        $applicant = Applicant::findOrFail($applicantId);

        //Validate Input
        $request->validate([
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'email' => ['nullable', 'string', 'email', 'max:191', Rule::unique('applicants')->ignore($applicantId)],
            'phone' => ['required', 'string', 'max:191', Rule::unique('applicants')->ignore($applicantId)],
            'id_number' => ['required', 'string', 'max:13', Rule::unique('applicants')->ignore($applicantId)],
            'employment' => ['required', 'in:A,B,I,N,P'],
            'gender_id' => ['required', 'integer', 'exists:genders,id'],
            'race_id' => ['required', 'integer', 'exists:races,id'],
            'location' => ['required', 'string'],
            'latitude' => ['required', function ($attribute, $value, $fail) {
                if (empty($value)) {
                    $fail('Please select a verified address from the Google suggestions.');
                }
            }],
            'longitude' => ['required', function ($attribute, $value, $fail) {
                if (empty($value)) {
                    $fail('Please select a verified address from the Google suggestions.');
                }
            }],
            'education_id' => ['required', 'integer', 'exists:educations,id'],
            'duration_id' => ['required', 'integer', 'exists:durations,id'],
            'brands' => ['required', 'array', function ($attribute, $value, $fail) {
                // Check if brand ID 1 is in the array and there are other IDs selected
                if (in_array(1, $value) && count($value) > 1) {
                    $fail('You cannot select specific brands with "Any".');
                }
            }], // Ensure brands is an array
            'brands.*' => ['required', 'integer', 'exists:brands,id'], // Validate each brand id exists in the brands table
            'public_holidays' => ['required', 'in:Yes,No'],
            'environment' => ['required', 'in:Yes,No'],
            'disability' => ['required', 'in:Yes,No'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
        ]);

        try {
            // Get form data from the request
            $firstname = $request->firstname;
            $lastname = $request->lastname;
            $email = $request->email;
            $phone = $request->phone;
            $idNumber = $request->id_number;
            $employment = $request->employment;
            $genderId = $request->gender_id;
            $raceId = $request->race_id;
            $location = $request->location;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $coordinates = $latitude . ',' . $longitude;
            $educationId = $request->education_id;
            $durationId = $request->duration_id;
            $publicHolidays = $request->public_holidays;
            $environment = $request->environment;
            $disability = $request->disability;
            $state_id = $request->state_id;

            DB::beginTransaction();

            // Applicant Create
            $applicant->update([
                'phone' => $phone,
                'id_number' => $idNumber,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'gender_id' => $genderId,
                'race_id' => $raceId,
                'contact_number' => $phone,
                'public_holidays' => $publicHolidays,
                'education_id' => $educationId,
                'environment' => $environment,
                'duration_id' => $durationId,
                'location' => $location,
                'coordinates' => $coordinates,
                'email' => $email,
                'disability' => $disability,
                'employment' => $employment,
                'state_id' => $state_id,
            ]);

            // Brands
            if ($request->has('brands')) {
                // Get the submitted brands
                $brands = $request->brands;

                // If brand with ID 2 is present, add 3 and 4 to the array
                if (in_array(2, $brands)) {
                    $brands = array_merge($brands, [3, 4]);
                }

                // Prepare the data to sync with timestamps
                $brandData = array_fill_keys($brands, ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);

                // Sync the brands with the applicant
                $applicant->brands()->sync($brandData);
            }

            // Check if the shortlist ID should be cleared
            if (empty($request->shortlist_id) && !empty($applicant->shortlist_id)) {
                // Find the shortlist with the current applicant's shortlist_id
                $shortlist = Shortlist::find($applicant->shortlist_id);

                if ($shortlist) {
                    // Decode applicant_ids to array, remove the applicant ID, and re-encode it
                    $applicantIds = json_decode($shortlist->applicant_ids, true);

                    if (($key = array_search($applicant->id, $applicantIds)) !== false) {
                        unset($applicantIds[$key]);
                        $shortlist->applicant_ids = json_encode(array_values($applicantIds)); // Re-index array
                        $shortlist->save();
                    }
                }

                // Clear the applicant's shortlist_id
                $applicant->shortlist_id = null;
                $applicant->save();
            }

            // Delete interviews if interview_id is empty and applicant has interviews
            if (empty($request->interview_id) && $applicant->interviews()->exists()) {
                $applicant->interviews()->where('applicant_id', $applicant->id)->delete();
            }

            // Clear appointed record if appointed_id is empty and applicant has an appointed record
            if (empty($request->appointed_id) && !empty($applicant->appointed_id)) {
                // Retrieve the VacancyFill record
                $vacancyFill = VacancyFill::find($applicant->appointed_id);

                if ($vacancyFill) {
                    // Delete the associated SAP Number record, if it exists
                    if ($vacancyFill->sapNumbers) {
                        $vacancyFill->sapNumbers()->delete();
                    }

                    // Delete the VacancyFill record
                    $vacancyFill->delete();

                    // Set applicant's appointed_id to null
                    $applicant->appointed_id = null;
                    $applicant->save();
                }
            }

            DB::commit(); // Commit the transaction

            // Dispatch a background job for ID number processing
            ProcessUserIdNumber::dispatch(null, $applicant->id);

            // Dispatch the new job to process the ID number, pass the applicant object as well
            SendIdNumberToSap::dispatch($applicant->id_number, $applicant);

            // Encrypt the applicant's ID before sending it in the response
            $encryptedID = Crypt::encryptString($applicant->id);

            return response()->json([
                'success' => true,
                'message' => 'Application updated successfully!',
                'applicant' => $applicant,
                'encrypted_id' => $encryptedID
            ], 201); // Return success response with applicant data
        } catch (Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of error

            return response()->json([
                'success' => false,
                'message' => 'Failed to update application!',
                'error' => $e->getMessage()
            ], 400); // Return error response
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant Delete
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $applicantID = Crypt::decryptString($id);

            $delete = Applicant::findOrFail($applicantID);
            $delete->delete();

            return response()->json([
                'success' => true,
                'message' => 'Applicant deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete applicant!',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant Delete Multiple
    |--------------------------------------------------------------------------
    */

    public function destroyMultiple(Request $request)
    {
        try {
            $ids = $request->input('ids');

            if (is_null($ids) || empty($ids)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No IDs provided'
                ], 400);
            }

            // Decrypt IDs
            $decryptedIds = array_map(function ($id) {
                return Crypt::decryptString($id);
            }, $ids);

            DB::beginTransaction();

            User::destroy($decryptedIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Applicants deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete applicants!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant Pagination
    |--------------------------------------------------------------------------
    */

    public function fetchApplicants(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10); // Default to 10 items per page
            $applicants = Applicant::with(['town', 'gender', 'race', 'education', 'duration', 'brands', 'state'])
                ->orderBy('firstname')
                ->orderBy('lastname')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'current_page' => $applicants->currentPage(),
                'last_page' => $applicants->lastPage(),
                'prev_page_url' => $applicants->previousPageUrl(),
                'next_page_url' => $applicants->nextPageUrl(),
                'data' => $applicants->items(),
                'path' => $applicants->path(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch applicants!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
