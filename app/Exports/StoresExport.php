<?php

namespace App\Exports;

use Carbon\Carbon;
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

class StoresExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping, WithTitle
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
        return 'Stores';
    }

    /**
     * Retrieve the vacancies based on filters.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Start building the query for stores
        $query = Store::query();

        // Apply date range filter
        $query->whereBetween('created_at', [$this->startDate, $this->endDate]);

        // Apply additional filters
        if (isset($this->filters['brand_id'])) {
            $query->where('brand_id', $this->filters['brand_id']);
        }

        if (isset($this->filters['province_id'])) {
            $query->whereHas('town', function ($q) {
                $q->where('province_id', $this->filters['province_id']);
            });
        }

        if (isset($this->filters['town_id'])) {
            $query->where('town_id', $this->filters['town_id']);
        }

        if (isset($this->filters['division_id'])) {
            $query->where('division_id', $this->filters['division_id']);
        }

        if (isset($this->filters['region_id'])) {
            $query->where('region_id', $this->filters['region_id']);
        }

        if (isset($this->filters['store_id'])) {
            if (is_array($this->filters['store_id'])) {
                $query->whereIn('id', $this->filters['store_id']);
            }
        }

        // Return the collection of filtered stores
        return $query->get();
    }

    /**
     * Map data for each row.
     *
     * @param mixed $store
     * @return array
     */
    public function map($store): array
    {
        // Get total vacancies for the store
        $totalVacancies = $store->vacancies->count() ?? 0;

        // Count total interviews conducted (score is not null)
        $totalInterviewsConducted = $store->vacancies->reduce(function ($carry, $vacancy) {
            return $carry + $vacancy->interviews()->whereNotNull('score')->count();
        }, 0);

        // Count total applicants placed at this store
        $totalApplicantsPlaced = $store->vacancies->reduce(function ($carry, $vacancy) {
            return $carry + $vacancy->appointed()->count();
        }, 0);

        // Calculate Hire to Interview Ratio
        $hireToInterviewRatio = $totalApplicantsPlaced > 0
        ? '1 to ' . round($totalInterviewsConducted / $totalApplicantsPlaced, 1)
        : '0 to 0';

        // Calculate percentage of successful interviews
        $successfulInterviewsPercentage = $totalInterviewsConducted > 0
        ? round(($totalApplicantsPlaced / $totalInterviewsConducted) * 100)
        : 0;    

        // Calculate Average Time to Shortlist
        $averageTimeToShortlist = $this->calculateAverageTimeToShortlist($store);

        // Calculate Average Time to Hire
        $averageTimeToHire = $this->calculateAverageTimeToHire($store);

        // Calculate Average Distance of Applicants Placed
        $averageDistanceApplicantsPlaced = $this->calculateAverageDistanceApplicantsPlaced($store);

        // Calculate Average Assessment Score of Applicants Placed
        $averageAssessmentScoreApplicantsPlaced = $this->calculateAverageAssessmentScoreApplicantsPlaced($store);

        return [
            optional($store->brand)->name ?? '',
            optional($store->division)->name ?? '',
            optional($store->region)->name ?? '',
            $store->code ?? '',            
            $store->name ?? '',
            optional(optional($store->town)->province)->name ?? '',
            optional($store->town)->name ?? '',
            $store->address ?? '',
            $totalVacancies === 0 ? '0' : ($totalVacancies ?? '0'),
            $totalApplicantsPlaced === 0 ? '0' : ($totalApplicantsPlaced ?? '0'),
            $totalInterviewsConducted === 0 ? '0' : ($totalInterviewsConducted ?? '0'),
            $successfulInterviewsPercentage . '%',
            $averageTimeToShortlist,
            $averageTimeToHire,
            $averageDistanceApplicantsPlaced . 'km',
            $averageAssessmentScoreApplicantsPlaced . '%',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Brand',
            'Division',
            'Region',
            'Store Code',            
            'Branch Name',
            'Province',
            'Town',          
            'Address',
            'Total Vacancies',
            'Total Applicants Placed',
            'Total Interviews Conducted',
            'Percentage of Interviews Successful',
            'Average Time to Shortlist',
            'Average Time to Hire',
            'Average Distance of Applicants Placed',
            'Average Assement Score of Applicants Placed',
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
        $sheet->getStyle('A:P')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Format specific columns for numbers (e.g., Code)
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode('0'); // Code as an integer
        $sheet->getStyle('I')->getNumberFormat()->setFormatCode('0'); // Total vaacncies as an integer
        $sheet->getStyle('J')->getNumberFormat()->setFormatCode('0'); // Total applicants as an integer
        $sheet->getStyle('K')->getNumberFormat()->setFormatCode('0'); // Total interviews as an integer

        // Set left alignment and wrap text for all relevant cells
        $sheet->getStyle('I')->getAlignment()->setWrapText(true);
        $sheet->getStyle('J')->getAlignment()->setWrapText(true);
        $sheet->getStyle('K')->getAlignment()->setWrapText(true);
        $sheet->getStyle('L')->getAlignment()->setWrapText(true);
        $sheet->getStyle('M')->getAlignment()->setWrapText(true);
        $sheet->getStyle('N')->getAlignment()->setWrapText(true);
        $sheet->getStyle('O')->getAlignment()->setWrapText(true);
        $sheet->getStyle('P')->getAlignment()->setWrapText(true);

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
            'D' => 15, // Store Code            
            'E' => 30, // Branch Name
            'F' => 20, // Province
            'G' => 20, // Town
            'H' => 35, // Address
            'I' => 20, // Total Vacancies
            'J' => 20, // Total Interviews Conducted
            'K' => 20, // Total Applicants Placed
            'L' => 20, // Percentage of Interviews Successfull
            'M' => 20, // Average Time to Shortlist
            'N' => 20, // Average Time to Hire
            'O' => 20, // Average Distance of Applicants Placed
            'P' => 20, // Average Assessment Score of Applicants Placed
        ];
    }

    /**
     * Calculate the average time to shortlist for a store.
     *
     * @param mixed $store
     * @return string
     */
    private function calculateAverageTimeToShortlist($store): string
    {
        $totalTimeInSeconds = 0;
        $shortlistCount = 0;

        // Loop through all vacancies and their shortlists
        foreach ($store->vacancies as $vacancy) {
            foreach ($vacancy->shortlists as $shortlist) {
                // Calculate the time difference in seconds
                $totalTimeInSeconds += $vacancy->created_at->diffInSeconds($shortlist->created_at);
                $shortlistCount++;
            }
        }

        // Calculate the average time in seconds
        if ($shortlistCount > 0) {
            $averageTimeInSeconds = $totalTimeInSeconds / $shortlistCount;

            // Convert total seconds into days, hours, minutes, and seconds
            $totalDays = floor($averageTimeInSeconds / (24 * 3600));
            $remainingSeconds = $averageTimeInSeconds % (24 * 3600);

            $totalHours = floor($remainingSeconds / 3600);
            $remainingSeconds %= 3600;

            $totalMinutes = floor($remainingSeconds / 60);
            $totalSeconds = $remainingSeconds % 60;

            // Format the time based on its length
            if ($totalDays == 0 && $totalHours == 0) {
                return sprintf('%dM %dS', $totalMinutes, $totalSeconds);
            }
            if ($totalDays == 0) {
                return sprintf('%dH %dM', $totalHours, $totalMinutes);
            }
            return sprintf('%dD %dH %dM', $totalDays, $totalHours, $totalMinutes);
        }

        // Default value if there are no shortlists
        return '0D 0H 0M';
    }

    /**
     * Calculate the average time to hire for a store.
     *
     * @param mixed $store
     * @return string
     */
    private function calculateAverageTimeToHire($store): string
    {
        $totalTimeInSeconds = 0;
        $hiringCount = 0;

        // Loop through all vacancies and their appointed applicants
        foreach ($store->vacancies as $vacancy) {
            $firstAppointment = $vacancy->appointed()->orderBy('vacancy_fills.created_at', 'asc')->first();

            if ($firstAppointment) {
                // Calculate the time difference in seconds
                $totalTimeInSeconds += $vacancy->created_at->diffInSeconds($firstAppointment->pivot->created_at);
                $hiringCount++;
            }
        }

        // Calculate the average time in seconds
        if ($hiringCount > 0) {
            $averageTimeInSeconds = $totalTimeInSeconds / $hiringCount;

            // Convert total seconds into days, hours, minutes, and seconds
            $totalDays = floor($averageTimeInSeconds / (24 * 3600));
            $remainingSeconds = $averageTimeInSeconds % (24 * 3600);

            $totalHours = floor($remainingSeconds / 3600);
            $remainingSeconds %= 3600;

            $totalMinutes = floor($remainingSeconds / 60);
            $totalSeconds = $remainingSeconds % 60;

            // Format the time based on its length
            if ($totalDays == 0 && $totalHours == 0) {
                return sprintf('%dM %dS', $totalMinutes, $totalSeconds);
            }
            if ($totalDays == 0) {
                return sprintf('%dH %dM', $totalHours, $totalMinutes);
            }
            return sprintf('%dD %dH %dM', $totalDays, $totalHours, $totalMinutes);
        }

        // Default value if there are no hires
        return '0D 0H 0M';
    }

    /**
     * Calculate the average distance of appointed applicants from the store.
     *
     * @param mixed $store
     * @return float
     */
    private function calculateAverageDistanceApplicantsPlaced($store): float
    {
        $totalDistance = 0;
        $applicantCount = 0;

        // Ensure the store has valid coordinates
        if ($store->coordinates) {
            $storeCoordinates = explode(',', $store->coordinates); // Assuming coordinates are stored as "latitude,longitude"
            $storeLat = floatval($storeCoordinates[0]);
            $storeLng = floatval($storeCoordinates[1]);

            // Loop through all vacancies and their appointed applicants
            foreach ($store->vacancies as $vacancy) {
                foreach ($vacancy->appointed as $applicant) {
                    // Assuming applicants have a 'coordinates' field in the format "latitude,longitude"
                    if ($applicant->coordinates) {
                        $applicantCoordinates = explode(',', $applicant->coordinates);
                        $applicantLat = floatval($applicantCoordinates[0]);
                        $applicantLng = floatval($applicantCoordinates[1]);

                        // Calculate the distance between the store and the applicant in kilometers
                        $distance = $this->calculateDistance($storeLat, $storeLng, $applicantLat, $applicantLng);
                        $totalDistance += $distance;
                        $applicantCount++;
                    }
                }
            }
        }

        // Calculate the average distance and round it to 1 decimal place
        return $applicantCount > 0 ? round($totalDistance / $applicantCount, 1) : 0;
    }

    /**
     * Calculate the distance between two coordinates (latitude and longitude) in kilometers.
     *
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371; // Radius of the Earth in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in kilometers
    }

    /**
     * Calculate the average assessment score of appointed applicants for a store.
     *
     * @param mixed $store
     * @return float
     */
    private function calculateAverageAssessmentScoreApplicantsPlaced($store): float
    {
        $totalAssessmentPercentage = 0;
        $applicantCount = 0;

        // Loop through all vacancies and their appointed applicants
        foreach ($store->vacancies as $vacancy) {
            foreach ($vacancy->appointed as $applicant) {
                // Calculate the literacy percentage
                $literacyPercentage = $applicant->literacy_questions > 0
                    ? ($applicant->literacy_score / $applicant->literacy_questions) * 100
                    : 0;

                // Calculate the numeracy percentage
                $numeracyPercentage = $applicant->numeracy_questions > 0
                    ? ($applicant->numeracy_score / $applicant->numeracy_questions) * 100
                    : 0;

                // Calculate the situational percentage
                $situationalPercentage = $applicant->situational_questions > 0
                    ? ($applicant->situational_score / $applicant->situational_questions) * 100
                    : 0;

                // Calculate the average percentage of the three assessments for this applicant
                $averageApplicantAssessmentPercentage = ($literacyPercentage + $numeracyPercentage + $situationalPercentage) / 3;

                // Sum up the average percentages
                $totalAssessmentPercentage += $averageApplicantAssessmentPercentage;
                $applicantCount++;
            }
        }

        // Calculate the overall average assessment score percentage
        return $applicantCount > 0
            ? round($totalAssessmentPercentage / $applicantCount, 2) // Return average rounded to 2 decimal places
            : 0; // Default to 0 if no applicants found
    }
}
