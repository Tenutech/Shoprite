<?php

namespace App\Exports;

use Carbon\Carbon;
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
use Illuminate\Support\Facades\Crypt;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\Log;

class ApplicantsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping, WithTitle
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
        // Start building the query for applicants
        $query = Applicant::query();

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

                // Apply geographic filters for shortlisted applicants
                if (isset($this->filters['division_id'])) {
                    $query->whereHas('shortlist.vacancy.store', function ($storeQuery) {
                        $storeQuery->where('division_id', $this->filters['division_id']);
                    });
                } elseif (isset($this->filters['region_id'])) {
                    $query->whereHas('shortlist.vacancy.store', function ($storeQuery) {
                        $storeQuery->where('region_id', $this->filters['region_id']);
                    });
                } elseif (isset($this->filters['store_id'])) {
                    $query->whereHas('shortlist.vacancy', function ($vacancyQuery) {
                        $vacancyQuery->where('store_id', $this->filters['store_id']);
                    });
                }
            } elseif ($this->filters['shortlisted'] === 'No') {
                $query->whereNull('shortlist_id');
            }
        }

        // Interviewed filter
        if (isset($this->filters['interviewed'])) {
            if ($this->filters['interviewed'] === 'Yes') {
                $query->whereHas('interviews', function ($interviewQuery) {
                    $interviewQuery->whereNotNull('score');
                });
            } elseif ($this->filters['interviewed'] === 'No') {
                $query->where(function ($q) {
                    $q->doesntHave('interviews')
                    ->orWhereHas('interviews', function ($interviewQuery) {
                        $interviewQuery->whereNull('score');
                    });
                });
            }

            if (isset($this->filters['division_id'])) {
                $query->whereHas('interviews.vacancy.store', function ($storeQuery) {
                    $storeQuery->where('division_id', $this->filters['division_id']);
                });
            } elseif (isset($this->filters['region_id'])) {
                $query->whereHas('interviews.vacancy.store', function ($storeQuery) {
                    $storeQuery->where('region_id', $this->filters['region_id']);
                });
            } elseif (isset($this->filters['store_id'])) {
                $query->whereHas('interviews.vacancy', function ($vacancyQuery) {
                    $vacancyQuery->where('store_id', $this->filters['store_id']);
                });
            }
        }

        // Appointed filter
        if (isset($this->filters['appointed']) && $this->filters['appointed'] === 'Yes') {
            $query->whereNotNull('appointed_id') // Only include appointed applicants
                ->whereHas('vacanciesFilled', function ($vacancyQuery) {
                    // Apply the date range to `vacancy_fills.created_at`
                    $vacancyQuery->whereBetween('vacancy_fills.created_at', [$this->startDate, $this->endDate]);

                    // Apply geographic filters for appointed applicants
                    if (isset($this->filters['division_id'])) {
                        $vacancyQuery->whereHas('store', function ($storeQuery) {
                            $storeQuery->where('division_id', $this->filters['division_id']);
                        });
                    } elseif (isset($this->filters['region_id'])) {
                        $vacancyQuery->whereHas('store', function ($storeQuery) {
                            $storeQuery->where('region_id', $this->filters['region_id']);
                        });
                    } elseif (isset($this->filters['store_id'])) {
                        $vacancyQuery->where('store_id', $this->filters['store_id']);
                    }
                });
        } else {
            // Default date range filter for non-appointed applicants
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }

        // Store proximity and type filtering
        if ((!isset($this->filters['shortlisted']) || $this->filters['shortlisted'] == 'No') && (!isset($this->filters['interviewed']) || $this->filters['interviewed'] == 'No') && (!isset($this->filters['appointed']) || $this->filters['appointed'] == 'No') && (isset($this->filters['store_id']) || isset($this->filters['region_id']) || isset($this->filters['division_id']))) {
            // Get stores based on the filter priority: division -> region -> store
            $stores = Store::when(isset($this->filters['division_id']), function ($query) {
                    return $query->where('division_id', $this->filters['division_id']);
                })
                ->when(isset($this->filters['region_id']), function ($query) {
                    return $query->where('region_id', $this->filters['region_id']);
                })
                ->when(isset($this->filters['store_id']), function ($query) {
                    return $query->where('id', $this->filters['store_id']);
                })
                ->get();

            // Early return if no stores match the criteria
            if ($stores->isEmpty()) {
                return collect([]); // Return an empty collection
            }

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
        // Retrieve the complete state ID
        $completeStateID = State::where('code', 'complete')->value('id');

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
            $applicant->terms_conditions ?? '',
            $applicant->public_holidays,
            $applicant->environment,
            $applicant->consent ?? '',
            $applicant->disability ?? '',
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
            'Terms & Conditions',
            'Shift Basis',
            'Work Environment',
            'Background Check',
            'Disability',
            'Assessment Score',
            'Overall Score',
            'Application Channel',
            'Drop off',
            'State',
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
        $sheet->getStyle('A:AC')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Format specific columns for numbers (e.g., ID Number and Phone)
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // ID Number as an integer
        $sheet->getStyle('J')->getNumberFormat()->setFormatCode('0'); // Phone Number as an integer
        $sheet->getStyle('AC')->getNumberFormat()->setFormatCode('0'); // SAP Number as an integer

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
            'R' => 20, // Terms & Conditions
            'S' => 20, // Shift Basis
            'T' => 20, // Work Environment
            'U' => 20, // Background Check
            'V' => 15, // Disability
            'W' => 20, // Assessment Score
            'X' => 20, // Overall Score
            'Y' => 20, // Application Channel
            'Z' => 15, // Drop off
            'AA' => 25, // State
            'AB' => 15, // State
            'AC' => 15, // State
        ];
    }
}
