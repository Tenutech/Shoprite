<?php

namespace App\Exports;

use App\Models\Region;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RegionsExport implements FromGenerator, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function generator(): \Generator
    {
        $query = Region::select(
            'regions.name as name',
            'divisions.name as division',
            'stores.name as store_name',
            'stores.address as address',
            'towns.name as town'
        )
            ->leftJoin('divisions', 'regions.division_id', '=', 'divisions.id')
            ->leftJoin('stores', 'regions.id', '=', 'stores.region_id')
            ->leftJoin('towns', 'towns.id', '=', 'stores.town_id')
            ->when($this->search, fn($q) => $this->applySearch($q, $this->search))
            ->orderBy('divisions.name');

        foreach ($query->cursor() as $store) {
            yield [
                $store->name,
                $store->division,
                $store->store_name,
                $store->address,
                $store->town
            ];
        }
    }

    public function headings(): array
    {
        return [
            'Region',
            'Division',
            'Store Name',
            'Address',
            'Town'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold header row
        $styles = [
            1 => ['font' => ['bold' => true]],
        ];

        // Set left alignment and wrap text for all cells
        $sheet->getStyle('A:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // region
            'B' => 25, // division
            'C' => 20, // store_name
            'D' => 50, // address
            'E' => 20, // town
        ];
    }

    public function title(): string
    {
        return 'Regions Report';
    }

    private function applySearch($query, $search)
    {
        $query->where(function ($q) use ($search) {
            $q->where('regions.name', 'like', "%{$search}%")
                ->orWhere('divisions.name', 'like', "%{$search}%")
                ->orWhere('stores.name', 'like', "%{$search}%");
        });
    }
}
