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
        return EmailTemplate::with('role:id,name')
        ->select('name', 'subject', 'intro', 'role_id')
        ->get()
        ->map(function($emailTemplate) {
            return [
                'name' => $emailTemplate->name,
                'subject' => $emailTemplate->subject,
                'intro' => $emailTemplate->intro,
                'role' => optional($emailTemplate->role)->name,
            ];
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Name',
            'Subject',
            'Body',
            'Role'
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
            'B' => 52,
            'C' => 140,
            'D' => 20,
        ];
    }
}