<?php

namespace App\Exports;

use App\Models\Vacancy;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class VacanciesOverTimeExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $startDate = $this->filters['start_date'];
        $endDate = $this->filters['end_date'];

        // Filter vacancies based on input filters (e.g., position, store, date range)
        $vacancies = Vacancy::query()
            ->when($this->filters['position_id'], fn($query) => $query->where('position_id', $this->filters['position_id']))
            ->when($this->filters['store_id'], fn($query) => $query->where('store_id', $this->filters['store_id']))
            ->when($this->filters['type_id'], fn($query) => $query->where('type_id', $this->filters['type_id']))
            ->when($this->filters['filled_positions'], fn($query) => $query->where('filled_positions', $this->filters['filled_positions']))
            ->when($this->filters['start_date'] && $this->filters['end_date'], function ($query) {
                $query->whereBetween('created_at', [
                    Carbon::parse($this->filters['start_date']),
                    Carbon::parse($this->filters['end_date']),
                ]);
            })
            ->get();

        // Get all months for the given date range
        $months = [];
        $current = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->startOfMonth();

        while ($current <= $end) {
            $monthKey = $current->format('Y-F');
            $months[$monthKey] = ['Total Vacancies' => 0, 'Filled Vacancies' => 0, 'Total' => 0];
            $current->addMonth();
        }

        // Count total and filled vacancies by month
        $vacanciesOverTime = $vacancies->groupBy(fn($vacancy) => $vacancy->created_at->format('Y-F'))
            ->map(function ($group) {
                return [
                    'Total Vacancies' => $group->where('open_positions', '!=', 0)->count(),
                    'Filled Vacancies' => $group->where('open_positions', 0)->count(),
                    'Total' => $group->count(),
                ];
            })->toArray();

        $data = array_replace($months, $vacanciesOverTime);

        $exportData = [];

        // Convert to a collection and calculate grand totals
        $exportData = collect($data)->map(function ($counts, $month) {
            return [
                'Year-Month' => $month,
                'Total Vacancies' => $counts['Total Vacancies'] ?? 0,
                'Filled Vacancies' => $counts['Filled Vacancies'] ?? 0,
                'Total' => $counts['Total'] ?? 0,
            ];
        });

        // Calculate grand totals for each relevant column
        $grandTotal = [
            'Month' => 'Grand Total',
            'Total Vacancies' => $exportData->sum('Total Vacancies'),
            'Filled Vacancies' => $exportData->sum('Filled Vacancies'),
            'Total' => $exportData->sum('Total'),
        ];

        $exportData->push($grandTotal);

        // Return the collection
        return $exportData;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Month',
            'Total Vacancies',
            'Filled Vacancies',
            'Total',
        ];
    }

    /**
     * Apply styles to the worksheet.
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Make the headings bold
            1 => ['font' => ['bold' => true]],
            // Wrap text in columns
            'A' => ['alignment' => ['wrapText' => true]],
            'B' => ['alignment' => ['wrapText' => true]],
            'C' => ['alignment' => ['wrapText' => true]],
            'D' => ['alignment' => ['wrapText' => true]],
        ];
    }

    /**
     * Set column widths.
     *
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 10,
            'C' => 10,
            'D' => 10,
        ];
    }
}
