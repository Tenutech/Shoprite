<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Applicant;
use App\Models\Store;
use App\Models\State;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ApplicantsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping, WithTitle, WithChunkReading
{
    protected $type;
    protected $id;
    protected $startDate;
    protected $endDate;
    protected $maxDistanceFromStore;
    protected $filters;

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
     * Set the worksheet title.
     *
     * @return string
     */
    public function title(): string
    {
        return 'Applicants';
    }

    /**
     * Retrieve the applicants based on filters.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Start with the base query
        $query = Applicant::query()
            ->select([
                'applicants.id',
                'applicants.gender_id',
                'applicants.race_id',
                'applicants.education_id',
                'applicants.duration_id',
                'applicants.employment',
                'applicants.age',
                'applicants.literacy_score',
                'applicants.numeracy_score',
                'applicants.situational_score',
                'applicants.score',
                'applicants.state_id',
                'applicants.shortlist_id',
                'applicants.coordinates',
                'applicants.created_at'
            ]);
    
        // Apply all additional filters in bulk
        $filters = $this->filters;
    
        // Use conditional logic for each filter
        $query->when(isset($filters['gender_id']), fn($q) => $q->where('gender_id', $filters['gender_id']))
              ->when(isset($filters['race_id']), fn($q) => $q->where('race_id', $filters['race_id']))
              ->when(isset($filters['education_id']), fn($q) => $q->where('education_id', $filters['education_id']))
              ->when(isset($filters['duration_id']), fn($q) => $q->where('duration_id', $filters['duration_id']))
              ->when(isset($filters['employment']), fn($q) => $q->where('employment', $filters['employment']))
              ->when(isset($filters['min_age']) && isset($filters['max_age']), fn($q) => $q->whereBetween('age', [$filters['min_age'], $filters['max_age']]))
              ->when(isset($filters['min_literacy']) && isset($filters['max_literacy']), fn($q) => $q->whereBetween('literacy_score', [$filters['min_literacy'], $filters['max_literacy']]))
              ->when(isset($filters['min_numeracy']) && isset($filters['max_numeracy']), fn($q) => $q->whereBetween('numeracy_score', [$filters['min_numeracy'], $filters['max_numeracy']]))
              ->when(isset($filters['min_situational']) && isset($filters['max_situational']), fn($q) => $q->whereBetween('situational_score', [$filters['min_situational'], $filters['max_situational']]))
              ->when(isset($filters['min_overall']) && isset($filters['max_overall']), fn($q) => $q->whereBetween('score', [$filters['score'], $filters['score']]))
              ->when(isset($filters['completed']), function ($q) use ($filters) {
                  $completeStateID = State::where('code', 'complete')->value('id');
                  if ($filters['completed'] === 'Yes') {
                      $q->where('state_id', '>=', $completeStateID);
                  } elseif ($filters['completed'] === 'No') {
                      $q->where('state_id', '<', $completeStateID);
                  }
              });
    
        // Shortlisted filter
        if (isset($filters['shortlisted'])) {
            if ($filters['shortlisted'] === 'Yes') {
                $query->whereNotNull('shortlist_id')
                      ->when(isset($filters['division_id']), fn($q) => $q->whereHas('shortlist.vacancy.store', fn($sq) => $sq->where('division_id', $filters['division_id'])))
                      ->when(isset($filters['region_id']), fn($q) => $q->whereHas('shortlist.vacancy.store', fn($sq) => $sq->where('region_id', $filters['region_id'])))
                      ->when(isset($filters['store_id']), fn($q) => $q->whereHas('shortlist.vacancy', fn($vq) => $vq->whereIn('store_id', $filters['store_id'] ?? [])));
            } elseif ($filters['shortlisted'] === 'No') {
                $query->whereNull('shortlist_id');
            }
        }
    
        // Interviewed filter
        if (isset($filters['interviewed'])) {
            $query->when($filters['interviewed'] === 'Yes', fn($q) => $q->whereHas('interviews', fn($iq) => $iq->whereNotNull('score')))
                  ->when($filters['interviewed'] === 'No', fn($q) => $q->doesntHave('interviews')->orWhereHas('interviews', fn($iq) => $iq->whereNull('score')));
        }
    
        // Appointed filter
        if (isset($filters['appointed']) && $filters['appointed'] === 'Yes') {
            $query->whereNotNull('appointed_id')
                  ->whereHas('vacanciesFilled', function ($q) use ($filters) {
                      $q->whereBetween('vacancy_fills.created_at', [$this->startDate, $this->endDate])
                        ->when(isset($filters['division_id']), fn($q) => $q->whereHas('store', fn($sq) => $sq->where('division_id', $filters['division_id'])))
                        ->when(isset($filters['region_id']), fn($q) => $q->whereHas('store', fn($sq) => $sq->where('region_id', $filters['region_id'])))
                        ->when(isset($filters['store_id']), fn($q) => $q->whereIn('store_id', $filters['store_id'] ?? []));
                  });
        } else {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }
    
        // Proximity filtering (optimized for geospatial distance calculation)
        if (isset($filters['max_distance'], $filters['store_id']) || isset($filters['region_id']) || isset($filters['division_id'])) {
            $stores = Store::select('id', 'coordinates')
                           ->when(isset($filters['division_id']), fn($q) => $q->where('division_id', $filters['division_id']))
                           ->when(isset($filters['region_id']), fn($q) => $q->where('region_id', $filters['region_id']))
                           ->when(isset($filters['store_id']), fn($q) => $q->whereIn('id', $filters['store_id'] ?? []))
                           ->get();
    
            $query->where(function ($q) use ($stores, $filters) {
                foreach ($stores as $store) {
                    if ($store->coordinates) {
                        [$lat, $lng] = array_map('floatval', explode(',', $store->coordinates));
                        $q->orWhereRaw("ST_Distance_Sphere(
                            point(SUBSTRING_INDEX(applicants.coordinates, ',', -1), SUBSTRING_INDEX(applicants.coordinates, ',', 1)),
                            point(?, ?)) <= ?", [$lng, $lat, $filters['max_distance'] * 1000]);
                    }
                }
            });
        }
    
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
        // Retrieve the complete state ID
        $completeStateID = State::where('code', 'complete')->value('id');

        // Calculate individual literacy, numeracy, and situational percentages
        $literacyPercentage = isset($applicant->literacy_score, $applicant->literacy_questions) && $applicant->literacy_questions > 0
            ? round(($applicant->literacy_score / $applicant->literacy_questions) * 100)
            : '';

        $numeracyPercentage = isset($applicant->numeracy_score, $applicant->numeracy_questions) && $applicant->numeracy_questions > 0
            ? round(($applicant->numeracy_score / $applicant->numeracy_questions) * 100)
            : '';

        $situationalPercentage = isset($applicant->situational_score, $applicant->situational_questions) && $applicant->situational_questions > 0
            ? round(($applicant->situational_score / $applicant->situational_questions) * 100)
            : '';

        // Calculate assessment score percentage
        $assessmentScore = '';
        if (
            isset($applicant->literacy_score, $applicant->numeracy_score, $applicant->situational_score) &&
            isset($applicant->literacy_questions, $applicant->numeracy_questions, $applicant->situational_questions)
        ) {
            $totalScore = $applicant->literacy_score + $applicant->numeracy_score + $applicant->situational_score;
            $totalQuestions = $applicant->literacy_questions + $applicant->numeracy_questions + $applicant->situational_questions;
            if ($totalQuestions > 0) {
                $assessmentScore = round(($totalScore / $totalQuestions) * 100);
            }
        }

        // Get brands as a comma-separated string
        $brands = $applicant->brands->pluck('name')->join(', ');

        // Check if the applicant is appointed
        $appointed = $applicant->appointed_id ? 'Yes' : 'No';

        // Retrieve the SAP Number if appointed
        $sapNumber = '';
        if ($appointed === 'Yes') {
            // Get the latest SAP number from the pivot data if available
            $sapNumber = $applicant->vacanciesFilled()->latest()->first()->pivot->sap_number ?? '';
        }

        return [
            $applicant->created_at->format('Y-m-d H:i'),
            $applicant->id_number ?? '',
            $applicant->documents()->latest()->first() ? url('documents/view/' . Crypt::encryptString($applicant->documents()->latest()->first()->id)) : '',
            $applicant->firstname ?? '',
            $applicant->lastname ?? '',
            $applicant->birth_date ? date('Y-m-d', strtotime($applicant->birth_date)) : '',
            $applicant->age ?? '',
            optional($applicant->gender)->name ?? '',
            optional($applicant->race)->name ?? '',
            $applicant->phone ?? '',
            $applicant->email ?? '',
            optional($applicant->education)->name ?? '',
            optional($applicant->duration)->name ?? '',
            optional($applicant->town)->name ?? '',
            optional(optional($applicant->town)->province)->name ?? '',
            $brands ?? '',
            $applicant->location ?? '',
            $applicant->location_type ?? '',
            $applicant->terms_conditions ?? '',
            $applicant->public_holidays,
            $applicant->environment,
            $applicant->consent ?? '',
            $applicant->disability ?? '',
            $literacyPercentage,
            $numeracyPercentage,
            $situationalPercentage,
            $assessmentScore ?? '',
            $applicant->score ?? '',
            $applicant->application_type ?? '',
            $applicant->state_id < $completeStateID ? 'Yes' : 'No',
            optional($applicant->state)->name ?? '',
            $appointed ?? '',
            $sapNumber ?? '',
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
            'Application Date',
            'ID Number',
            'ID Image URL',
            'First Name',
            'Last Name',
            'Date of Birth',
            'Age',
            'Gender',
            'Race',
            'Phone Number',
            'Email Address',
            'Highest Qualification',
            'Experience',
            'Town',
            'Province',
            'Brands',
            'Home Address',
            'Location Type',
            'Terms & Conditions',
            'Shift Basis',
            'Work Environment',
            'Background Check',
            'Disability',
            'Literacy Score (%)',
            'Numeracy Score (%)',
            'Situational Awareness Score (%)',
            'Total Assessment Score (%)',
            'Overall Candidate Score',
            'Application Channel',
            'Drop off',
            'Workflow Stage',
            'Appointed',
            'SAP Number',
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
        // Bold header row
        $styles = [
            1 => ['font' => ['bold' => true]],
        ];

        // Set left alignment and wrap text for all cells
        $sheet->getStyle('A:AG')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Format specific columns for numbers (e.g., ID Number and Phone)
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // ID Number as an integer
        $sheet->getStyle('J')->getNumberFormat()->setFormatCode('0'); // Phone Number as an integer
        $sheet->getStyle('X')->getNumberFormat()->setFormatCode('0'); // Literacy Score as an integer
        $sheet->getStyle('Y')->getNumberFormat()->setFormatCode('0'); // Numeracy Score as an integer
        $sheet->getStyle('Z')->getNumberFormat()->setFormatCode('0'); // Situational Score as an integer
        $sheet->getStyle('AA')->getNumberFormat()->setFormatCode('0'); // Total Assessment Score as an integer
        $sheet->getStyle('AB')->getNumberFormat()->setFormatCode('0'); // Overall Score Score as an integer
        $sheet->getStyle('AG')->getNumberFormat()->setFormatCode('0'); // SAP Number as an integer

        return $styles;
    }

    /**
     * Define column widths for each column.
     *
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20, // Application Date
            'B' => 20, // ID Number
            'C' => 20, // ID Image URL
            'D' => 20, // First Name
            'E' => 20, // Last Name
            'F' => 15, // Date of Birth
            'G' => 10, // Age
            'H' => 15, // Gender
            'I' => 15, // Race
            'J' => 15, // Phone Number
            'K' => 25, // Email Address
            'L' => 20, // Highest Qualification
            'M' => 20, // Experience
            'N' => 15, // Town
            'O' => 15, // Province
            'P' => 25, // Brands
            'Q' => 40, // Home Address
            'R' => 15, // Location Type
            'S' => 20, // Terms & Conditions
            'T' => 20, // Shift Basis
            'U' => 20, // Work Environment
            'V' => 20, // Background Check
            'W' => 15, // Disability
            'X' => 15, // Literacy Score
            'Y' => 15, // Numeracy Score
            'Z' => 15, // Situational Awareness Score
            'AA' => 20, // Total Assessment Score
            'AB' => 20, // Overall Score
            'AC' => 20, // Application Channel
            'AD' => 15, // Drop off
            'AE' => 25, // State
            'AF' => 15, // Appointed
            'AG' => 15, // Sap Number
        ];
    }

    /**
     * Define the chunk size for reading data.
     */
    public function chunkSize(): int
    {
        return 500; // Adjust the chunk size as needed
    }
}
