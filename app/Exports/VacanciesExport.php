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
    protected $type, $id, $startDate, $endDate, $filters;

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
        // Prepare SAP numbers with line breaks
        $sapNumbers = $vacancy->sapNumbers->pluck('sap_number')->implode("\n");

        // Prepare appointed applicants with their sap numbers
        $appointedApplicants = $vacancy->appointed->map(function ($applicant) {
            return $applicant->firstname . ' ' . $applicant->lastname . ' (' . $applicant->id_number . ') - ' . $applicant->pivot->sap_number;
        })->implode("\n"); // Join each entry with a new line for better readability in Excel

        return [
            optional(optional($vacancy->store)->brand)->name ?? '',
            optional(optional($vacancy->store)->division)->name ?? '',
            optional(optional($vacancy->store)->region)->name ?? '',
            optional($vacancy->store)->name ?? '',
            optional($vacancy->store)->code ?? '', 
            optional($vacancy->position)->name ?? '',
            optional($vacancy->position)->description ?? '',
            $sapNumbers,
            optional($vacancy->type)->name ?? '',
            (optional($vacancy->user)->firstname ?? '') . ' ' . (optional($vacancy->user)->lastname ?? ''),           
            $vacancy->open_positions === 0 ? '0' : ($vacancy->open_positions ?? '0'),
            $vacancy->filled_positions === 0 ? '0' : ($vacancy->filled_positions ?? '0'),
            $appointedApplicants,
            $vacancy->created_at->format('Y-m-d H:i'),
            $vacancy->open_positions === 0 ? $vacancy->updated_at->format('Y-m-d H:i') : '',
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
            'Brand',
            'Division',
            'Region',
            'Branch Name',
            'Store Code', 
            'Position',
            'Position Description',
            'SAP Position Number(s)',
            'Position Type',
            'User',
            'Open Positions',
            'Filled Positions',
            'Successful Candidate(s)',
            'Created On',
            'Filled On',
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
        $sheet->getStyle('A:O')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Format specific columns for numbers (e.g., Open Positions and Filled Positions)
        $sheet->getStyle('K')->getNumberFormat()->setFormatCode('0'); // Open Positions as an integer
        $sheet->getStyle('L')->getNumberFormat()->setFormatCode('0'); // Filled Positions as an integer

        // Set left alignment and wrap text for all cells in the sap numbers column (H)
        $sheet->getStyle('H')->getAlignment()->setWrapText(true);

        // Set left alignment and wrap text for all cells in the successful candidate column (M)
        $sheet->getStyle('M')->getAlignment()->setWrapText(true);

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
            'F' => 25, // Position
            'G' => 35, // Position Description
            'H' => 25, // SAP Position Number(s)
            'I' => 25, // Position Type
            'J' => 20, // User
            'K' => 20, // Open Positions
            'L' => 20, // Filled Positions
            'M' => 50, // Successful Candidate(s)
            'N' => 20, // Created On
            'O' => 20, // Updated On
        ];
    }
}