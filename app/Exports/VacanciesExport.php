<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Vacancy;
use App\Models\Store;
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

class VacanciesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping, WithTitle
{
    protected $type;
    protected $id;
    protected $startDate;
    protected $endDate;
    protected $filters;

    public function __construct($type, $id, $startDate, $endDate, $filters)
    {
        $this->type = $type;
        $this->id = $id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filters = $filters;
    }

    /**
     * Set the worksheet title.
     *
     * @return string
     */
    public function title(): string
    {
        return 'Vacancies';
    }

    /**
     * Retrieve the vacancies based on filters.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Start building the query for vacancies
        $query = Vacancy::query();

        // Apply date range filter
        $query->whereBetween('created_at', [$this->startDate, $this->endDate]);

        // Apply all additional filters
        if (isset($this->filters['position_id'])) {
            $query->where('position_id', $this->filters['position_id']);
        }
        if (isset($this->filters['open_positions'])) {
            $query->where('open_positions', $this->filters['open_positions']);
        }
        if (isset($this->filters['filled_positions'])) {
            $query->where('filled_positions', $this->filters['filled_positions']);
        }
        if (isset($this->filters['brand_id'])) {
            $query->whereHas('store', function ($subQuery) {
                $subQuery->where('brand_id', $this->filters['brand_id']);
            });
        }
        if (isset($this->filters['division_id'])) {
            $query->whereHas('store', function ($subQuery) {
                $subQuery->where('division_id', $this->filters['division_id']);
            });
        }
        if (isset($this->filters['region_id'])) {
            $query->whereHas('store', function ($subQuery) {
                $subQuery->where('region_id', $this->filters['region_id']);
            });
        }
        if (isset($this->filters['store_id'])) {
            if (is_array($this->filters['store_id'])) {
                $query->whereIn('store_id', $this->filters['store_id']);
            }
        }
        if (isset($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }
        if (isset($this->filters['type_id'])) {
            $query->where('type_id', $this->filters['type_id']);
        }

        // Apply the `unactioned` filter
        if (isset($this->filters['unactioned'])) {
            if ($this->filters['unactioned'] === 'No') {
                // Get vacancies where shortlists exist and `applicant_ids` is not empty
                $query->whereHas('shortlists', function ($query) {
                    $query->whereNotNull('applicant_ids')->where('applicant_ids', '!=', '[]');
                });
            } elseif ($this->filters['unactioned'] === 'Yes') {
                // Get vacancies with no shortlists or where shortlists exist but `applicant_ids` is null or empty
                $query->whereDoesntHave('shortlists')
                    ->orWhereHas('shortlists', function ($query) {
                        $query->whereNull('applicant_ids')
                            ->orWhere('applicant_ids', '[]');
                    });
            }
        }

        // Apply the `deleted` filter
        if (isset($this->filters['deleted'])) {
            if ($this->filters['deleted'] === 'Auto') {
                $query->where('auto_deleted', 'Yes');
            } elseif ($this->filters['deleted'] === 'Manually') {
                $query->where('deleted', 'Yes')
                      ->where('auto_deleted', 'No');
            } elseif ($this->filters['deleted'] === 'No') {
                $query->where('deleted', 'No');
            }
        }

        // Return the collection of filtered vacancies
        return $query->get();
    }

    /**
     * Map data for each row.
     *
     * @param mixed $vacancy
     * @return array
     */
    public function map($vacancy): array
    {
        $rows = [];

        // Collect all SAP numbers from both sources
        $sapNumbers = $vacancy->sapNumbers ? $vacancy->sapNumbers->pluck('sap_number')->toArray() : [];
        $appointedSapNumbers = $vacancy->appointed->pluck('pivot.sap_number')->toArray();

        // Merge and remove duplicates
        $allSapNumbers = array_unique(array_merge($sapNumbers, $appointedSapNumbers));

        // Determine the total rows needed (whichever is greater: open_positions or available SAP numbers)
        $totalRows = max($vacancy->open_positions ?? 1, count($allSapNumbers));

        // Ensure at least `totalRows` SAP numbers (fill missing with null)
        $allSapNumbers = array_pad($allSapNumbers, $totalRows, null);

        // Map the appointed applicants by their SAP numbers
        $appointedApplicantsBySap = $vacancy->appointed->mapWithKeys(function ($applicant) {
            return [$applicant->pivot->sap_number => $applicant->firstname . ' ' . $applicant->lastname . ' (' . $applicant->id_number . ') - ' . $applicant->pivot->sap_number];
        });

        // Map the appointment dates by their SAP numbers
        $appointedDatesBySap = $vacancy->appointed->mapWithKeys(function ($applicant) {
            return [$applicant->pivot->sap_number => $applicant->pivot->created_at];
        });

        // Loop to create rows for each SAP number (or empty rows if required)
        foreach ($allSapNumbers as $index => $currentSapNumber) {
            // Determine open_positions and filled_positions
            $isFilled = isset($appointedApplicantsBySap[$currentSapNumber]);
            $openPositions = $isFilled ? '0' : 1;
            $filledPositions = $isFilled ? 1 : '0';

            // Determine Vacancy Status
            if ($vacancy->deleted === 'Yes' && $vacancy->auto_deleted === 'Yes') {
                $vacancyStatus = 'Auto Deleted';
            } elseif ($vacancy->deleted === 'Yes' && $vacancy->auto_deleted === 'No') {
                $vacancyStatus = 'Manually Deleted';
            } else {
                $vacancyStatus = 'Not Deleted';
            }

            // Calculate the difference in days between created_at and updated_at
            $daysDifference = $vacancy->created_at->diffInDays($vacancy->updated_at);

            $rows[] = [
                optional(optional($vacancy->store)->brand)->name ?? '',
                optional(optional($vacancy->store)->division)->name ?? '',
                optional(optional($vacancy->store)->region)->name ?? '',
                optional($vacancy->store)->name ?? '',
                optional($vacancy->store)->code ?? '',
                optional($vacancy->position)->name ?? '',
                $currentSapNumber ?? '', // Ensure SAP number is empty if null
                optional($vacancy->type)->name ?? '',
                (optional($vacancy->user)->firstname ?? '') . ' ' . (optional($vacancy->user)->lastname ?? ''),
                $openPositions, // Open positions for this SAP number
                $filledPositions, // Filled positions for this SAP number
                $appointedApplicantsBySap[$currentSapNumber] ?? '', // Match appointed applicant to the current SAP number
                $vacancy->created_at->format('Y-m-d H:i'),
                $isFilled ? $appointedDatesBySap[$currentSapNumber]->format('Y-m-d H:i') : '', // Updated "Filled On" column
                $vacancyStatus, // Vacancy Status column
                $daysDifference // Difference in days between created_at and updated_at
            ];
        }

        return $rows;
    }

    /**
     * Define column headings.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Brand',
            'Division',
            'Region',
            'Branch Name',
            'Branch Code',
            'Position Description',
            'SAP Position Number(s)',
            'Position Type',
            'User',
            'Open Positions',
            'Filled Positions',
            'Successful Candidate(s)',
            'Created On',
            'Filled On',
            'Vacancy Status',
            'Days',
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
        $sheet->getStyle('A:N')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Format specific columns for numbers (e.g., Open Positions and Filled Positions)
        $sheet->getStyle('J')->getNumberFormat()->setFormatCode('0'); // Open Positions as an integer
        $sheet->getStyle('K')->getNumberFormat()->setFormatCode('0'); // Filled Positions as an integer

        // Set left alignment and wrap text for all cells in the sap numbers column (G)
        $sheet->getStyle('G')->getAlignment()->setWrapText(true);

        // Set left alignment and wrap text for all cells in the successful candidate column (L)
        $sheet->getStyle('L')->getAlignment()->setWrapText(true);

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
            'A' => 20, // Brand
            'B' => 20, // Region
            'C' => 20, // Division
            'D' => 25, // Branch Name
            'E' => 20, // Store Code
            'F' => 30, // Position Description
            'G' => 25, // SAP Position Number(s)
            'H' => 25, // Position Type
            'I' => 20, // User
            'J' => 20, // Open Positions
            'K' => 20, // Filled Positions
            'L' => 50, // Successful Candidate(s)
            'M' => 20, // Created On
            'N' => 20, // Updated On
            'O' => 25, // Vacancy Status
            'P' => 20, // Days
        ];
    }
}
