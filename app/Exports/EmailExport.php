<?php

namespace App\Exports;

use App\Models\EmailTemplate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmailExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Select the columns you want to export
        return EmailTemplate::select('subject', 'intro')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Subject',
            'Body'
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
            'A' => 52,
            'B' => 140,
        ];
    }
}
