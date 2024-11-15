<?php

namespace App\Exports;

use App\Models\Store;
use App\Services\DataService\Reports\StoreDataService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StoresOverTimeExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters, StoreDataService $storeDataService)
    {
        $this->filters = $filters;
        $this->storeDataService = $storeDataService;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $stores = Store::all();
        $data = [];

        foreach ($stores as $store) {
            $filters = array_merge($this->filters, ['type' => 'store', 'id' => $store->id]);

            $totalApplicantsPlaced = $this->storeDataService->getTotalCompletedApplicants($this->filters);
            $averageTimeToHire = $this->storeDataService->getAverageTimeToHire($this->filters);
            $averageAssessmentScore = $this->storeDataService->getAverageAssessmentScoreApplicantsAppointed($this->filters);
            $averageDistanceApplicantsAppointed = $this->storeDataService->getAverageDistanceApplicantsAppointed($this->filters);
            $shortlistToHireRatio = $this->storeDataService->getShortlistToHireRatio($this->filters);
            $interviewToHireRatio = $this->storeDataService->getInterviewToHireRatio($this->filters);

            $data[] = [
                'Store Name' => $store->name,
                'Total Applicants Placed' => $totalApplicantsPlaced,
                'Average Time to Hire' => $averageTimeToHire,
                'Average Assessment Score' => $averageAssessmentScore,
                'Average Distance of Applicants Appointed' => $averageDistanceApplicantsAppointed,
                'Shortlist to Hire Ratio' => $shortlistToHireRatio,
                'Interview to Hire Ratio' => $interviewToHireRatio,
            ];
        }

        return new Collection($data);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Store',
            'Total Applicant Placed',
            'Average Time to Hire',
            'Average Assement Score',
            'Average Distance Applicants Appointed',
            'Shortlist To Hire Ratio',
            'Interview To Hire Ratio',
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
            'G' => ['alignment' => ['wrapText' => true]],
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
            'A' => 100,
            'B' => 30,
            'C' => 30,
            'D' => 30,
            'E' => 30,
            'F' => 30,
            'G' => 30,
        ];
    }
}
