<?php

namespace App\Exports;

use App\Models\Vacancy;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class VacancyTypesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, ShouldAutoSize
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
            $months[$monthKey] = ['FullTime' => 0, 'PartTime' => 0, 'FixedTerm' => 0, 'PeakSeason' => 0, 'Total' => 0];
            $current->addMonth();
        }

        // Group and count vacancies by month and type
        $vacanciesByYearMonth = $vacancies->groupBy(fn($vacancy) => $vacancy->created_at->format('Y-F'))
            ->map(function ($group) {
                return [
                    'FullTime' => $group->where('type_id', 1)->count(),
                    'PartTime' => $group->where('type_id', 2)->count(),
                    'FixedTerm' => $group->where('type_id', 3)->count(),
                    'PeakSeason' => $group->where('type_id', 4)->count(),
                    'Total' => $group->count(),
                ];
            })->toArray();

        $data = array_replace($months, $vacanciesByYearMonth);

        $exportData = [];

        foreach ($data as $month => $counts) {
            $exportData[] = [
                'Year-Month' => $month,
                'FullTime' => $counts['FullTime'],
                'PartTime' => $counts['PartTime'],
                'FixedTerm' => $counts['FixedTerm'],
                'PeakSeason' => $counts['PeakSeason'],
                'Total' => $counts['Total'],
            ];
        }

        // Return as a Collection for Excel
        return new Collection($exportData);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Month',
            'Full Time',
            'Part Time',
            'Fixed Term',
            'Peak Season',
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
            'E' => ['alignment' => ['wrapText' => true]],
            'F' => ['alignment' => ['wrapText' => true]],
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
            'E' => 10,
            'F' => 10,
        ];
    }
}
