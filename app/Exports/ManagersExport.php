<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ManagersExport implements FromGenerator, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function generator(): \Generator
    {
        $query = User::select(
            'users.firstname as first_name',
            'users.lastname as last_name',
            'users.phone',
            'users.email',
            'stores.name as store',
            'regions.name as region',
            'divisions.name as division',
            'brands.name as brand'
        )
            ->leftJoin('stores', 'users.store_id', '=', 'stores.id')
            ->leftJoin('regions', 'users.region_id', '=', 'regions.id')
            ->leftJoin('divisions', 'users.division_id', '=', 'divisions.id')
            ->leftJoin('brands', 'users.brand_id', '=', 'brands.id')
            ->where('users.role_id', 6)
            ->when($this->search, fn($q) => $this->applySearch($q, $this->search))
            ->orderBy('divisions.name')
            ->orderBy('users.lastname');

        foreach ($query->cursor() as $user) {
            yield [
                $user->first_name,
                $user->last_name,
                ' ' . $user->phone, // ensure phone is treated as string
                $user->email,
                $user->store,
                $user->region,
                $user->division,
                $user->brand
            ];
        }
    }

    public function headings(): array
    {
        return [
            'First Name',
            'Last Name',
            'Phone',
            'Email',
            'Store',
            'Region',
            'Division',
            'Brand'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold header row
        $styles = [
            1 => ['font' => ['bold' => true]],
        ];

        // Set left alignment and wrap text for all cells
        $sheet->getStyle('A:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Set left alignment and wrap text for all relevant cells
        $sheet->getStyle('E')->getAlignment()->setWrapText(true);
        $sheet->getStyle('F')->getAlignment()->setWrapText(true);

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // fist_name
            'B' => 25, // last_name
            'C' => 20, // phone
            'D' => 35, // email
            'E' => 30, // store
            'F' => 25, // region
            'G' => 25, // division
            'H' => 15, // brand
        ];
    }

    public function title(): string
    {
        return 'Managers Report';
    }

    private function applySearch($query, $search)
    {
        $query->where(function ($q) use ($search) {
            $q->where('users.firstname', 'like', "%{$search}%")
                ->orWhere('users.lastname', 'like', "%{$search}%")
                ->orWhere('users.phone', 'like', "%{$search}%")
                ->orWhere('users.email', 'like', "%{$search}%")
                ->orWhere('stores.name', 'like', "%{$search}%")
                ->orWhere('divisions.name', 'like', "%{$search}%");
        });
    }
}
