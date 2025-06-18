<?php

namespace App\Exports;

use App\Models\Division;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DivisionsExport implements FromGenerator, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function generator(): \Generator
    {
        $query = Division::select(
            'name'
        )
            ->when($this->search, fn($q) => $this->applySearch($q, $this->search))
            ->orderBy('name');

        foreach ($query->cursor() as $division) {
            yield [
                $division->name
            ];
        }
    }

    public function headings(): array
    {
        return [
            'Division Name'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold header row
        $styles = [
            1 => ['font' => ['bold' => true]],
        ];

        // Set left alignment and wrap text for all cells
        $sheet->getStyle('A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // name
        ];
    }

    public function title(): string
    {
        return 'Divisions Report';
    }

    private function applySearch($query, $search)
    {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        });
    }
}
