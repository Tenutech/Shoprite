<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Applicant;
use App\Models\Store;
use App\Models\State;
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

class ApplicantsExport implements FromQuery, WithHeadings, WithStyles, WithColumnWidths, WithMapping, WithTitle, WithChunkReading
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
    public function query()
    {
        $query = Applicant::query()
            ->select([
                'applicants.id',
                'applicants.created_at',
                'applicants.id_number',
                'applicants.firstname',
                'applicants.lastname',
                'applicants.birth_date',
                'applicants.age',
                'applicants.literacy_score',
                'applicants.literacy_questions',
                'applicants.numeracy_score',
                'applicants.numeracy_questions',
                'applicants.situational_score',
                'applicants.situational_questions',
                'applicants.score',
                'applicants.phone',
                'applicants.email',
                'applicants.location',
                'applicants.location_type',
                'applicants.terms_conditions',
                'applicants.public_holidays',
                'applicants.environment',
                'applicants.consent',
                'applicants.disability',
                'applicants.application_type',
                'applicants.state_id',
                'states.name as state_name',
                'educations.name as education_name',
                'durations.name as duration_name',
                'genders.name as gender_name',
                'races.name as race_name',
                'towns.name as town_name',
                'provinces.name as province_name',
                DB::raw("
                    (
                        SELECT GROUP_CONCAT(DISTINCT brands.name SEPARATOR ', ')
                        FROM applicant_brands
                        JOIN brands ON applicant_brands.brand_id = brands.id
                        WHERE applicant_brands.applicant_id = applicants.id
                    ) as brand_names
                "),
                DB::raw("
                    (
                        SELECT vacancy_fills.sap_number
                        FROM vacancy_fills
                        WHERE vacancy_fills.applicant_id = applicants.id
                        ORDER BY vacancy_fills.created_at DESC
                        LIMIT 1
                    ) as latest_sap_number
                ")
            ])
            ->leftJoin('states', 'applicants.state_id', '=', 'states.id')
            ->leftJoin('educations', 'applicants.education_id', '=', 'educations.id')
            ->leftJoin('durations', 'applicants.duration_id', '=', 'durations.id')
            ->leftJoin('genders', 'applicants.gender_id', '=', 'genders.id')
            ->leftJoin('races', 'applicants.race_id', '=', 'races.id')
            ->leftJoin('towns', 'applicants.town_id', '=', 'towns.id')
            ->leftJoin('provinces', 'towns.province_id', '=', 'provinces.id');

        // Apply filters
        if (isset($this->filters['gender_id'])) {
            $query->where('applicants.gender_id', $this->filters['gender_id']);
        }

        if (isset($this->filters['race_id'])) {
            $query->where('applicants.race_id', $this->filters['race_id']);
        }

        if (isset($this->filters['education_id'])) {
            $query->where('applicants.education_id', $this->filters['education_id']);
        }

        if (isset($this->filters['duration_id'])) {
            $query->where('applicants.duration_id', $this->filters['duration_id']);
        }

        if (isset($this->filters['employment'])) {
            $query->where('applicants.employment', $this->filters['employment']);
        }

        if (isset($this->filters['min_age']) && isset($this->filters['max_age'])) {
            $query->whereBetween('applicants.age', [$this->filters['min_age'], $this->filters['max_age']]);
        }

        if (isset($this->filters['min_literacy']) && isset($this->filters['max_literacy'])) {
            $query->whereBetween('applicants.literacy_score', [$this->filters['min_literacy'], $this->filters['max_literacy']]);
        }

        if (isset($this->filters['min_numeracy']) && isset($this->filters['max_numeracy'])) {
            $query->whereBetween('applicants.numeracy_score', [$this->filters['min_numeracy'], $this->filters['max_numeracy']]);
        }

        if (isset($this->filters['min_situational']) && isset($this->filters['max_situational'])) {
            $query->whereBetween('applicants.situational_score', [$this->filters['min_situational'], $this->filters['max_situational']]);
        }

        if (isset($this->filters['min_overall']) && isset($this->filters['max_overall'])) {
            $query->whereBetween('applicants.score', [$this->filters['min_overall'], $this->filters['max_overall']]);
        }

        if (isset($this->filters['completed'])) {
            $completeStateID = State::where('code', 'complete')->value('id');
            if ($this->filters['completed'] === 'Yes') {
                $query->where('applicants.state_id', '>=', $completeStateID);
            } elseif ($this->filters['completed'] === 'No') {
                $query->where('applicants.state_id', '<', $completeStateID);
            }
        }

        if (isset($this->filters['shortlisted'])) {
            $query->whereNotNull('applicants.shortlist_id');
        }

        if (isset($this->filters['interviewed'])) {
            $query->whereHas('interviews', function ($interviewQuery) {
                $interviewQuery->whereNotNull('score');
            });
        }

        if (isset($this->filters['appointed'])) {
            $query->whereNotNull('applicants.appointed_id');
        }

        $query->whereBetween('applicants.created_at', [$this->startDate, $this->endDate]);

        return $query;
    }

    /**
     * Map data for each row.
     *
     * @param mixed $applicant
     * @return array
     */
    public function map($applicant): array
    {
        // If literacy_questions exists and > 0, calculate percentage; otherwise, show an empty string.
        $literacyPercentage = ($applicant->literacy_questions ?? 0) > 0
            ? round(($applicant->literacy_score / $applicant->literacy_questions) * 100)
            : '';

        // Same logic for numeracy
        $numeracyPercentage = ($applicant->numeracy_questions ?? 0) > 0
            ? round(($applicant->numeracy_score / $applicant->numeracy_questions) * 100)
            : '';

        // Same logic for situational
        $situationalPercentage = ($applicant->situational_questions ?? 0) > 0
            ? round(($applicant->situational_score / $applicant->situational_questions) * 100)
            : '';

        // Calculate the total assessment score only if at least one of literacy, numeracy, situational questions is > 0
        $totalQuestions = ($applicant->literacy_questions ?? 0)
                        + ($applicant->numeracy_questions ?? 0)
                        + ($applicant->situational_questions ?? 0);

        $assessmentScore = '';
        if ($totalQuestions > 0) {
            $totalScore = ($applicant->literacy_score ?? 0)
                        + ($applicant->numeracy_score ?? 0)
                        + ($applicant->situational_score ?? 0);

            $assessmentScore = round(($totalScore / $totalQuestions) * 100);
        }

        return [
            $applicant->created_at->format('Y-m-d H:i'),
            $applicant->id_number ?? '',
            $applicant->firstname ?? '',
            $applicant->lastname ?? '',
            $applicant->birth_date ? date('Y-m-d', strtotime($applicant->birth_date)) : '',
            $applicant->age ?? '',
            $applicant->gender_name ?? '',
            $applicant->race_name ?? '',
            $applicant->phone ?? '',
            $applicant->email ?? '',
            $applicant->education_name ?? '',
            $applicant->duration_name ?? '',
            $applicant->town_name ?? '',
            $applicant->province_name ?? '',
            $applicant->brand_names ?? '',
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
            $assessmentScore,
            $applicant->score ?? '',
            $applicant->application_type ?? '',
            $applicant->state_id < $this->completeStateID ? 'Yes' : 'No',
            $applicant->state_name ?? '',
            $applicant->appointed_id ? 'Yes' : 'No',
            $applicant->latest_sap_number ?? ''
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
            'A' => 20, // Application Date
            'B' => 20, // ID Number
            'C' => 20, // First Name
            'D' => 20, // Last Name
            'E' => 15, // Date of Birth
            'F' => 10, // Age
            'G' => 15, // Gender
            'H' => 15, // Race
            'I' => 15, // Phone Number
            'J' => 25, // Email Address
            'K' => 20, // Highest Qualification
            'L' => 20, // Experience
            'M' => 15, // Town
            'N' => 15, // Province
            'O' => 25, // Brands
            'P' => 40, // Home Address
            'Q' => 15, // Location Type
            'R' => 20, // Terms & Conditions
            'S' => 20, // Shift Basis
            'T' => 20, // Work Environment
            'U' => 20, // Background Check
            'V' => 15, // Disability
            'W' => 15, // Literacy Score
            'X' => 15, // Numeracy Score
            'Y' => 15, // Situational Awareness Score
            'Z' => 20, // Total Assessment Score
            'AA' => 20, // Overall Score
            'AB' => 20, // Application Channel
            'AC' => 15, // Drop off
            'AD' => 25, // State
            'AE' => 15, // Appointed
            'AF' => 15, // Sap Number
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
