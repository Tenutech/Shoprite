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
     * Retrieve the applicants based on filters.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Start building the query for applicants
        $query = Vacancy::query();

        // Apply date range filter
        $query->whereBetween('created_at', [$this->startDate, $this->endDate]);

        // Apply all additional filters
        if (isset($filters['position_id'])) {
            $query->where('position_id', $filters['position_id']);
        }
        if (isset($filters['open_positions'])) {
            $query->where('open_positions', $filters['open_positions']);
        }
        if (isset($filters['filled_positions'])) {
            $query->where('filled_positions', $filters['filled_positions']);
        }
        if (isset($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (isset($filters['type_id'])) {
            $query->where('type_id', $filters['type_id']);
        }

        // Return the collection of filtered applicants
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
        // Prepare appointed applicants with their sap numbers
        $appointedApplicants = $vacancy->appointed->map(function ($applicant) {
            return $applicant->firstname . ' ' . $applicant->lastname . ' - ' . $applicant->pivot->sap_number;
        })->implode("\n"); // Join each entry with a new line for better readability in Excel

        return [
            optional($vacancy->position)->name ?? '',
            (optional($vacancy->store)->code ?? '') . ' - (' . (optional(optional($vacancy->store)->brand)->name ?? '') . ') ' . (optional($vacancy->store)->name ?? ''),
            (optional($vacancy->user)->firstname ?? '') . ' ' . (optional($vacancy->user)->lastname ?? ''),
            optional($vacancy->type)->name ?? '',
            $vacancy->open_positions === 0 ? '0' : ($vacancy->open_positions ?? '0'),
            $vacancy->filled_positions === 0 ? '0' : ($vacancy->filled_positions ?? '0'),
            $appointedApplicants,
            $vacancy->created_at->format('Y-m-d H:i'),
            $vacancy->updated_at->format('Y-m-d H:i'),
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
            'Position',
            'Store',
            'User',
            'Type',
            'Open Positions',
            'Filled Positions',
            'Appointed',
            'Created At',
            'Updated At',
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
        $sheet->getStyle('A:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Format specific columns for numbers (e.g., Open Positions and Filled Positions)
        $sheet->getStyle('E')->getNumberFormat()->setFormatCode('0'); // Open Positions as an integer
        $sheet->getStyle('F')->getNumberFormat()->setFormatCode('0'); // Filled Positions as an integer

        // Set left alignment and wrap text for all cells in the appointed column (I)
        $sheet->getStyle('G')->getAlignment()->setWrapText(true);

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
            'A' => 25, // Position
            'B' => 35, // Store
            'C' => 20, // User
            'D' => 20, // Type
            'E' => 20, // Open Positions
            'F' => 20, // Filled Positions
            'G' => 35, // Appointed
            'H' => 20, // Created At
            'I' => 20, // Updated At
        ];
    }
}