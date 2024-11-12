<?php

namespace App\Exports;

use App\Models\Applicant;
use App\Models\Store;
use App\Models\State;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Sheet;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ApplicantsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    protected $type, $id, $startDate, $endDate, $maxDistanceFromStore, $filters;

    public function __construct($type, $id, $startDate, $endDate, $maxDistanceFromStore, $filters)
    {
        $this->type = $type;
        $this->id = $id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->maxDistanceFromStore = $maxDistanceFromStore;
        $this->filters = $filters;
    }

    /**
     * Retrieve the applicants based on filters.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Start building the query for applicants
        $query = Applicant::query();

        // Apply date range filter
        $query->whereBetween('created_at', [$this->startDate, $this->endDate]);

        // Apply all additional filters
        if (isset($this->filters['gender_id'])) {
            $query->where('gender_id', $this->filters['gender_id']);
        }
        if (isset($this->filters['race_id'])) {
            $query->where('race_id', $this->filters['race_id']);
        }
        if (isset($this->filters['education_id'])) {
            $query->where('education_id', $this->filters['education_id']);
        }
        if (isset($this->filters['experience_id'])) {
            $query->where('experience_id', $this->filters['experience_id']);
        }
        if (isset($this->filters['employment'])) {
            $query->where('employment', $this->filters['employment']);
        }

        // Age, literacy, numeracy, situational, and overall score filters
        if (isset($this->filters['min_age']) && isset($this->filters['max_age'])) {
            $query->whereBetween('age', [$this->filters['min_age'], $this->filters['max_age']]);
        }
        if (isset($this->filters['min_literacy']) && isset($this->filters['max_literacy'])) {
            $query->whereBetween('literacy_score', [$this->filters['min_literacy'], $this->filters['max_literacy']]);
        }
        if (isset($this->filters['min_numeracy']) && isset($this->filters['max_numeracy'])) {
            $query->whereBetween('numeracy_score', [$this->filters['min_numeracy'], $this->filters['max_numeracy']]);
        }
        if (isset($this->filters['min_situational']) && isset($this->filters['max_situational'])) {
            $query->whereBetween('situational_score', [$this->filters['min_situational'], $this->filters['max_situational']]);
        }
        if (isset($this->filters['min_overall']) && isset($this->filters['max_overall'])) {
            $query->whereBetween('overall_score', [$this->filters['min_overall'], $this->filters['max_overall']]);
        }

        // Completed filter
        if (isset($this->filters['completed'])) {
            $completeStateID = State::where('code', 'complete')->value('id');
            if ($this->filters['completed'] === 'Yes') {
                $query->where('state_id', '>=', $completeStateID);
            } elseif ($this->filters['completed'] === 'No') {
                $query->where('state_id', '<', $completeStateID);
            }
        }

        // Shortlisted filter
        if (isset($this->filters['shortlisted'])) {
            if ($this->filters['shortlisted'] === 'Yes') {
                $query->whereNotNull('shortlist_id');
            } elseif ($this->filters['shortlisted'] === 'No') {
                $query->whereNull('shortlist_id');
            }
        }

        // Interviewed filter
        if (isset($this->filters['interviewed'])) {
            if ($this->filters['interviewed'] === 'Yes') {
                $query->whereHas('interviews');
            } elseif ($this->filters['interviewed'] === 'No') {
                $query->doesntHave('interviews');
            }
        }

        // Appointed filter
        if (isset($this->filters['appointed'])) {
            if ($this->filters['appointed'] === 'Yes') {
                $query->whereNotNull('appointed_id');
            } elseif ($this->filters['appointed'] === 'No') {
                $query->whereNull('appointed_id');
            }
        }

        // Store proximity and type filtering
        if (isset($this->filters['store_id'])) {
            // Filter by specific store
            $store = Store::find($this->filters['store_id']);
            if ($store && $store->coordinates) {
                [$storeLat, $storeLng] = array_map('floatval', explode(',', $store->coordinates));
                $query->whereRaw("ST_Distance_Sphere(
                    point(SUBSTRING_INDEX(applicants.coordinates, ',', -1), SUBSTRING_INDEX(applicants.coordinates, ',', 1)), 
                    point(?, ?)) <= ?", [$storeLng, $storeLat, $this->maxDistanceFromStore * 1000]);
            }
        } elseif ($this->type !== 'all') {
            // Loop through stores based on type (store, division, or region)
            $stores = Store::when($this->type === 'store', fn($q) => $q->where('id', $this->id))
                ->when($this->type === 'division', fn($q) => $q->where('division_id', $this->id))
                ->when($this->type === 'region', fn($q) => $q->where('region_id', $this->id))
                ->get();

            // Build a query for each store in proximity if applicable
            $storeQueries = collect([]);
            foreach ($stores as $store) {
                if ($store->coordinates) {
                    [$storeLat, $storeLng] = array_map('floatval', explode(',', $store->coordinates));
                    $storeQuery = clone $query;
                    $storeQuery->whereRaw("ST_Distance_Sphere(
                        point(SUBSTRING_INDEX(applicants.coordinates, ',', -1), SUBSTRING_INDEX(applicants.coordinates, ',', 1)), 
                        point(?, ?)) <= ?", [$storeLng, $storeLat, $this->maxDistanceFromStore * 1000]);
                    $storeQueries->push($storeQuery);
                }
            }
            // Combine all store queries
            return $storeQueries->map(fn($q) => $q->get())->flatten();
        }

        // Return the collection of filtered applicants
        return $query->get();
    }

    /**
     * Map data for each row.
     *
     * @param mixed $applicant
     * @return array
     */
    public function map($applicant): array
    {
        return [
            $applicant->created_at->format('Y-m-d H:i:s'),
            $applicant->id_number,
            $applicant->first_name,
            $applicant->last_name,
            $applicant->date_of_birth ? $applicant->date_of_birth->format('Y-m-d') : null,
            $applicant->age,
            $applicant->gender,
            $applicant->cell_number,
            $applicant->rsa_id_image_url,
            $applicant->primary_contact_number,
            $applicant->email,
            $applicant->agreed_to_terms ? 'Yes' : 'No',
            $applicant->shift_basis,
            $applicant->work_environment,
            $applicant->highest_qualification,
            $applicant->background_check ? 'Yes' : 'No',
            $applicant->experience,
            $applicant->province,
            $applicant->brands,
            $applicant->race,
            $applicant->home_address,
            $applicant->picture_uploaded ? 'Yes' : 'No',
            $applicant->disability,
            $applicant->assessment_score,
            $applicant->application_source,
            $applicant->is_duplicate ? 'Yes' : 'No',
            $applicant->dropped_off ? 'Yes' : 'No',
            $applicant->drop_off_stage,
        ];
    }

    /**
     * Define column headings.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Application Date/Time',
            'ID Number',
            'First Name',
            'Last Name',
            'Date of Birth',
            'Age',
            'Gender',
            'Cell Number',
            'RSA ID Image URL',
            'Primary Contact Number',
            'Email Address',
            'Agreed to Terms & Conditions',
            'Shift Basis?',
            'Work Environment',
            'Highest Qualification',
            'Condones Background Check',
            'Experience',
            'Province',
            'Brands (Shoprite/Checkers/Usave/All)',
            'Race',
            'Home Address',
            'Picture Uploaded',
            'Disability',
            'Assessment Score',
            'Source of Application (WhatsApp/URL)',
            'Duplicate Application',
            'Drop off? Yes/No',
            'If Dropped Off, Where in Process?',
        ];
    }

    /**
     * Apply bold styling to the header row.
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Define column widths for each column.
     *
     * @return array
     */
    public function columnWidths(): array
    {
        return array_fill_keys(range('A', 'AB'), 25);
    }
}