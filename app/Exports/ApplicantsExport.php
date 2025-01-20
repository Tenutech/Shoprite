<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Applicant;
use App\Models\Store;
use App\Models\State;
use App\DTO\ApplicantDTO;
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
use Illuminate\Support\Facades\Crypt;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ApplicantsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithChunkReading
{
    protected $type;
    protected $id;
    protected $startDate;
    protected $endDate;
    protected $maxDistanceFromStore;
    protected $filters;
    protected $completeStateID;

    public function __construct($type, $id, $startDate, $endDate, $maxDistanceFromStore, $filters)
    {
        $this->type = $type;
        $this->id = $id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->maxDistanceFromStore = $maxDistanceFromStore;
        $this->filters = $filters;
        $this->completeStateID = State::where('code', 'complete')->value('id');
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
     * @return \Illuminate\Database\Query\Builder
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

        return $query->cursor()->map(function ($applicant) {
            return new ApplicantDTO($applicant);
        });
    }


    /**
     * Map data for each row.
     *
     * @param mixed $applicant
     * @return array
     */
    // public function map($applicant): array
    // {
    //     // If literacy_questions exists and > 0, calculate percentage; otherwise, show an empty string.
    //     $literacyPercentage = ($applicant->literacy_questions ?? 0) > 0
    //         ? round(($applicant->literacy_score / $applicant->literacy_questions) * 100)
    //         : '';

    //     // Same logic for numeracy
    //     $numeracyPercentage = ($applicant->numeracy_questions ?? 0) > 0
    //         ? round(($applicant->numeracy_score / $applicant->numeracy_questions) * 100)
    //         : '';

    //     // Same logic for situational
    //     $situationalPercentage = ($applicant->situational_questions ?? 0) > 0
    //         ? round(($applicant->situational_score / $applicant->situational_questions) * 100)
    //         : '';

    //     // Calculate the total assessment score only if at least one of literacy, numeracy, situational questions is > 0
    //     $totalQuestions = ($applicant->literacy_questions ?? 0)
    //                     + ($applicant->numeracy_questions ?? 0)
    //                     + ($applicant->situational_questions ?? 0);

    //     $assessmentScore = '';
    //     if ($totalQuestions > 0) {
    //         $totalScore = ($applicant->literacy_score ?? 0)
    //                     + ($applicant->numeracy_score ?? 0)
    //                     + ($applicant->situational_score ?? 0);

    //         $assessmentScore = round(($totalScore / $totalQuestions) * 100);
    //     }

    //     // Check if the applicant is appointed
    //     $appointed = $applicant->appointed_id ? 'Yes' : 'No';

    //     return [
    //         $applicant->created_at->format('Y-m-d H:i'),
    //         $applicant->id_number ?? '',
    //         $applicant->firstname ?? '',
    //         $applicant->lastname ?? '',
    //         $applicant->birth_date ? date('Y-m-d', strtotime($applicant->birth_date)) : '',
    //         $applicant->age ?? '',
    //         $applicant->gender_name ?? '',
    //         $applicant->race_name ?? '',
    //         $applicant->phone ?? '',
    //         $applicant->email ?? '',
    //         $applicant->education_name ?? '',
    //         $applicant->duration_name ?? '',
    //         $applicant->town_name ?? '',
    //         $applicant->province_name ?? '',
    //         $applicant->brand_names ?? '',
    //         $applicant->location ?? '',
    //         $applicant->location_type ?? '',
    //         $applicant->terms_conditions ?? '',
    //         $applicant->public_holidays,
    //         $applicant->environment,
    //         $applicant->consent ?? '',
    //         $applicant->disability ?? '',
    //         $literacyPercentage,
    //         $numeracyPercentage,
    //         $situationalPercentage,
    //         $assessmentScore,
    //         $applicant->score ?? '',
    //         $applicant->application_type ?? '',
    //         $applicant->state_id < $this->completeStateID ? 'Yes' : 'No',
    //         $applicant->state_name ?? '',
    //         $appointed,
    //         $applicant->latest_sap_number ?? ''
    //     ];
    // }

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
        $sheet->getStyle('A:AF')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Format specific columns for numbers (e.g., ID Number and Phone)
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // ID Number as an integer
        $sheet->getStyle('I')->getNumberFormat()->setFormatCode('0'); // Phone Number as an integer
        $sheet->getStyle('W')->getNumberFormat()->setFormatCode('0'); // Literacy Score as an integer
        $sheet->getStyle('X')->getNumberFormat()->setFormatCode('0'); // Numeracy Score as an integer
        $sheet->getStyle('Y')->getNumberFormat()->setFormatCode('0'); // Situational Score as an integer
        $sheet->getStyle('Z')->getNumberFormat()->setFormatCode('0'); // Total Assessment Score as an integer
        $sheet->getStyle('AA')->getNumberFormat()->setFormatCode('0'); // Overall Score Score as an integer
        $sheet->getStyle('AF')->getNumberFormat()->setFormatCode('0'); // SAP Number as an integer

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
            'As' => 20, // ID Number
        ];
    }

    /**
     * Define the chunk size for reading data.
     */
    public function chunkSize(): int
    {
        return 1000; // Adjust the chunk size as needed
    }
}
