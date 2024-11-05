<?php

namespace App\Services\DataService\Reports;

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\State;
use App\Models\Vacancy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StoreDataService
{
    /**
     * Get the total number of completed applicants within a given date range (state_id >= completed).
     *
     * @param array $filters An optional array with 'from' and 'to' keys to specify date range filtering.
     * @return int The total count of completed applicants.
     */
    public function getTotalCompletedApplicants(array $filters = []): int
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $type = $filters['type'] ?? null;
        $id = $filters['id'] ?? null;

        // Start building the query using the Interview model, filter for appointed applicants and date range
        $interviews = Interview::where('status', 'Appointed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Prioritize filtering by store, followed by division, then region using Eloquent relationships
        if ($type === 'store') {
            $interviews->whereHas('vacancy', function ($query) use ($id) {
                $query->where('store_id', $id);  // Directly filter by store_id in vacancies table
            });
        } elseif ($type === 'division') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('division_id', $id);  // Filter by division_id in stores table
            });
        } elseif ($type === 'region') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('region_id', $id);  // Filter by region_id in stores table
            });
        }

        // Return the total count of appointed applicants
        return $interviews->count();
    }

    /**
     * Calculate the average time to hire for a specific type within a date range.
     *
     * @param array $filters An optional array with 'from' and 'to' keys to specify date range filtering.
     * @return string The average time to hire in the format 'D H M' (Days, Hours, Minutes).
     */
    public function getAverageTimeToHire(array $filters = []): string
    {
        // Set date range from filters if available
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $type = $filters['type'] ?? null;
        $id = $filters['id'] ?? null;

        // Retrieve vacancies within the specified date range and apply type filtering
        $vacancies = Vacancy::when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('created_at', [$startDate, $endDate]);
        })
                ->when($type === 'store', function ($query) use ($id) {
                    return $query->where('store_id', $id);
                })
                ->when($type === 'division', function ($query) use ($id) {
                    return $query->whereHas('store', function ($q) use ($id) {
                        $q->where('division_id', $id);
                    });
                })
                ->when($type === 'region', function ($query) use ($id) {
                    return $query->whereHas('store', function ($q) use ($id) {
                        $q->where('region_id', $id);
                    });
                })
                ->with(['appointed' => function ($query) {
                    $query->orderBy('vacancy_fills.created_at', 'asc'); // Order by first appointment
                }])
                ->get();

        $totalTimeInSeconds = 0;
        $hiringCount = 0;

        // Calculate the time difference for each vacancy's first appointed record
        foreach ($vacancies as $vacancy) {
            if ($vacancy->appointed->isNotEmpty()) {
                $firstAppointed = $vacancy->appointed->first();
                $totalTimeInSeconds += $vacancy->created_at->diffInSeconds($firstAppointed->pivot->created_at);
                $hiringCount++;
            }
        }

        // Calculate the average time to hire
        if ($hiringCount > 0) {
            $averageTimeInSeconds = $totalTimeInSeconds / $hiringCount;
            $totalDays = floor($averageTimeInSeconds / (24 * 3600));
            $remainingSeconds = $averageTimeInSeconds % (24 * 3600);
            $totalHours = floor($remainingSeconds / 3600);
            $remainingSeconds %= 3600;
            $totalMinutes = floor($remainingSeconds / 60);
            $totalSeconds = $remainingSeconds % 60;

            // Format return based on time components
            if ($totalDays == 0 && $totalHours == 0) {
                return sprintf('%dM %dS', $totalMinutes, $totalSeconds);
            }
            if ($totalDays == 0) {
                return sprintf('%dH %dM', $totalHours, $totalMinutes);
            }
            return sprintf('%dD %dH %dM', $totalDays, $totalHours, $totalMinutes);
        }

        // Default return if no appointments
        return '0D 0H 0M';
    }

    /**
     * Calculate the average assessment score (percentage) for all appointed applicants in vacancies filtered by type, ID, and date range.
     *
     * @param array $filters An optional array with keys:
     *                       - 'start_date' and 'end_date' for date range filtering,
     *                       - 'type' for the filter type (e.g., 'store', 'division', 'region'),
     *                       - 'id' for the specific ID to filter by.
     * @return float The average assessment score percentage of appointed applicants.
     */
    public function getAverageAssessmentScoreApplicantsAppointed(array $filters = []): float
    {
        // Extract filter values from the $filters array with default values if not present
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $type = $filters['type'] ?? null;
        $id = $filters['id'] ?? null;

        // Retrieve vacancies based on optional date range and type filters
        $vacancies = Vacancy::when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('created_at', [$startDate, $endDate]);
        })
                ->when($type === 'store', function ($query) use ($id) {
                    return $query->where('store_id', $id);
                })
                ->when($type === 'division', function ($query) use ($id) {
                    return $query->whereHas('store', function ($q) use ($id) {
                        $q->where('division_id', $id);
                    });
                })
                ->when($type === 'region', function ($query) use ($id) {
                    return $query->whereHas('store', function ($q) use ($id) {
                        $q->where('region_id', $id);
                    });
                })
                ->with(['appointed' => function ($query) {
                    // Only load appointed applicants with complete assessment data
                    $query->whereNotNull('literacy_score')
                        ->whereNotNull('literacy_questions')
                        ->whereNotNull('numeracy_score')
                        ->whereNotNull('numeracy_questions')
                        ->whereNotNull('situational_score')
                        ->whereNotNull('situational_questions');
                }])
                ->get();

        $totalAssessmentPercentage = 0;
        $applicantCount = 0;

        // Loop through each vacancy and its appointed applicants
        foreach ($vacancies as $vacancy) {
            foreach ($vacancy->appointed as $applicant) {
                // Calculate the literacy, numeracy, and situational percentages
                $literacyPercentage = $applicant->literacy_questions > 0
                    ? ($applicant->literacy_score / $applicant->literacy_questions) * 100
                    : 0;

                $numeracyPercentage = $applicant->numeracy_questions > 0
                    ? ($applicant->numeracy_score / $applicant->numeracy_questions) * 100
                    : 0;

                $situationalPercentage = $applicant->situational_questions > 0
                    ? ($applicant->situational_score / $applicant->situational_questions) * 100
                    : 0;

                // Calculate the average percentage for this applicant and accumulate the values
                $averageApplicantAssessmentPercentage = ($literacyPercentage + $numeracyPercentage + $situationalPercentage) / 3;
                $totalAssessmentPercentage += $averageApplicantAssessmentPercentage;
                $applicantCount++;
            }
        }

        // Calculate the overall average assessment score percentage
        return $applicantCount > 0
            ? round($totalAssessmentPercentage / $applicantCount, 2)
            : 0;
    }

    /**
     * Calculate the average distance between stores' coordinates and appointed applicants' coordinates based on filter criteria.
     *
     * @param array $filters An optional array with keys:
     *                       - 'start_date' and 'end_date' for date range filtering,
     *                       - 'type' for the filter type (e.g., 'store', 'division', 'region'),
     *                       - 'id' for the specific ID to filter by.
     * @return float The average distance in kilometers.
     */
    public function getAverageDistanceApplicantsAppointed(array $filters = []): float
    {
        $totalDistance = 0;
        $applicantCount = 0;

        // Extract filter values from the $filters array with default values if not present
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $type = $filters['type'] ?? null;
        $id = $filters['id'] ?? null;

        // Retrieve vacancies based on optional filters for date range, type, and ID
        $vacancies = Vacancy::when($type === 'store', function ($query) use ($id) {
                    return $query->where('store_id', $id);
        })
                ->when($type === 'division', function ($query) use ($id) {
                    return $query->whereHas('store', function ($q) use ($id) {
                        $q->where('division_id', $id);
                    });
                })
                ->when($type === 'region', function ($query) use ($id) {
                    return $query->whereHas('store', function ($q) use ($id) {
                        $q->where('region_id', $id);
                    });
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->with(['store', 'appointed']) // Load store and appointed applicants relationships
                ->get();

        // Loop through each vacancy to calculate distance for appointed applicants
        foreach ($vacancies as $vacancy) {
            $store = $vacancy->store;

            // Ensure the store has valid coordinates
            if ($store && $store->coordinates) {
                $storeCoordinates = explode(',', $store->coordinates); // Assuming coordinates are stored as "latitude,longitude"
                $storeLat = floatval($storeCoordinates[0]);
                $storeLng = floatval($storeCoordinates[1]);

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

        // Calculate the average distance and round to 1 decimal place
        return $applicantCount > 0
            ? round($totalDistance / $applicantCount, 1)
            : 0;
    }

    /**
     * Calculate the average time to shortlist for within a date range.
     *
     * @param array $filters An optional array with keys:
     *                       - 'start_date' and 'end_date' for date range filtering,
     *                       - 'type' for the filter type (e.g., 'store', 'division', 'region'),
     *                       - 'id' for the specific ID to filter by.
     * @return string
     */
    public function getAverageTimeToShortlist(array $filters = []): string
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $type = $filters['type'] ?? null;
        $id = $filters['id'] ?? null;

        // Retrieve vacancies within the specified date range using Eloquent and filter by store, division, or region
        $vacancies = Vacancy::whereBetween('created_at', [$startDate, $endDate])
            ->when($type === 'store', function ($query) use ($id) {
                return $query->where('store_id', $id);
            })
            ->when($type === 'division', function ($query) use ($id) {
                return $query->whereHas('store', function ($q) use ($id) {
                    $q->where('division_id', $id);
                });
            })
            ->when($type === 'region', function ($query) use ($id) {
                return $query->whereHas('store', function ($q) use ($id) {
                    $q->where('region_id', $id);
                });
            })
            ->with('shortlists')
            ->get();

        $totalTimeInSeconds = 0;
        $shortlistCount = 0;

        // Loop through each vacancy and its associated shortlists
        foreach ($vacancies as $vacancy) {
            foreach ($vacancy->shortlists as $shortlist) {
                // Calculate the time difference between vacancy creation and shortlist creation in seconds
                $totalTimeInSeconds += $vacancy->created_at->diffInSeconds($shortlist->created_at);
                $shortlistCount++;
            }
        }

        // Calculate the average time to shortlist
        if ($shortlistCount > 0) {
            $averageTimeInSeconds = $totalTimeInSeconds / $shortlistCount;

            // Convert total seconds into days, hours, minutes, and seconds
            $totalDays = floor($averageTimeInSeconds / (24 * 3600));
            $remainingSeconds = $averageTimeInSeconds % (24 * 3600);

            $totalHours = floor($remainingSeconds / 3600);
            $remainingSeconds %= 3600;

            $totalMinutes = floor($remainingSeconds / 60);
            $totalSeconds = $remainingSeconds % 60;

            // If the total time is less than one hour, return only minutes and seconds
            if ($totalDays == 0 && $totalHours == 0) {
                return sprintf('%dM %dS', $totalMinutes, $totalSeconds);
            }

            // If the total time is less than one day, return only hours and minutes
            if ($totalDays == 0) {
                return sprintf('%dH %dM', $totalHours, $totalMinutes);
            }

            // Otherwise, return the full format (days, hours, and minutes)
            return sprintf('%dD %dH %dM', $totalDays, $totalHours, $totalMinutes);
        }

        // Return default format if no shortlists
        return '0D 0H 0M';
    }

    /**
     * Get total number of completed interviews for a specific type within a date range.
     *
     * @param array $filters An optional array with keys:
     *                       - 'start_date' and 'end_date' for date range filtering,
     *                       - 'type' for the filter type (e.g., 'store', 'division', 'region'),
     *                       - 'id' for the specific ID to filter by.
     * @return int The total count of completed interviews.
     */
    public function getTotalInterviewsCompleted(array $filters = []): int
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $type = $filters['type'] ?? null;
        $id = $filters['id'] ?? null;

        // Start building the query using the Interview model, filter for completed interviews (score is not null) and date range
        $interviews = Interview::whereNotNull('score')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Prioritize filtering by store, followed by division, then region using Eloquent relationships
        if ($type === 'store') {
            $interviews->whereHas('vacancy', function ($query) use ($id) {
                $query->where('store_id', $id);  // Directly filter by store_id in vacancies table
            });
        } elseif ($type === 'division') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('division_id', $id);  // Filter by division_id in stores table
            });
        } elseif ($type === 'region') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('region_id', $id);  // Filter by region_id in stores table
            });
        }

        // Return the total count of completed interviews
        return $interviews->count();
    }

    /**
     * Get Total Applicants Appointedfor a specific type within a date range.
     *
     * @param array $filters An optional array with keys:
     *                       - 'start_date' and 'end_date' for date range filtering,
     *                       - 'type' for the filter type (e.g., 'store', 'division', 'region'),
     *                       - 'id' for the specific ID to filter by.
     * @return int The total count of appointed applicants.
     */
    public function getTotalApplicantsAppointed(array $filters = []): int
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $type = $filters['type'] ?? null;
        $id = $filters['id'] ?? null;

        // Start building the query using the Interview model, filter for appointed applicants and date range
        $interviews = Interview::where('status', 'Appointed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Prioritize filtering by store, followed by division, then region using Eloquent relationships
        if ($type === 'store') {
            $interviews->whereHas('vacancy', function ($query) use ($id) {
                $query->where('store_id', $id);  // Directly filter by store_id in vacancies table
            });
        } elseif ($type === 'division') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('division_id', $id);  // Filter by division_id in stores table
            });
        } elseif ($type === 'region') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('region_id', $id);  // Filter by region_id in stores table
            });
        }

        // Return the total count of appointed applicants
        return $interviews->count();
    }

    /**
     * Calculate the shortlist-to-hire ratio within a date range.
     *
     * @param array $filters An array of filters to apply.
     * @return string The shortlist-to-hire ratio in the format 'X:Y' where X is the average time to shortlist and Y is the average time to hire.
     */
    public function getShortlistToHireRatio(array $filters = []): string
    {
        // Get the average time to shortlist
        $averageShortlistTime = $this->getAverageTimeToShortlist($filters);
        // Get the average time to hire
        $averageHireTime = $this->getAverageTimeToHire($filters);

        // Convert average times to seconds for calculation
        $shortlistSeconds = $this->convertTimeToSeconds($averageShortlistTime);
        $hireSeconds = $this->convertTimeToSeconds($averageHireTime);

        // Calculate the ratio
        if ($hireSeconds > 0) {
            // Avoid division by zero and ensure the ratio is presented in a suitable format
            $ratio = ( $shortlistSeconds / $hireSeconds ) * 100;
            return sprintf('%.2f', $ratio);
        }

        return 'N/A';
    }

    /**
     * Calculate the interview-to-hire ratio within a date range.
     *
     * @param array $filters An array of filters to apply.
     * @return string The interview-to-hire ratio in the format 'X:Y' where X is the average time to interview and Y is the average time to hire.
     */
    public function getInterviewToHireRatio(array $filters = []): string
    {
        // Get the average time to interview
        $totalInterviewed = $this->getTotalInterviewsCompleted($filters);
        // Get the average time to hire
        $totalAppointed = $this->getTotalApplicantsAppointed($filters);

        // Calculate the ratio
        if ($totalAppointed > 0) {
            // Avoid division by zero and ensure the ratio is presented in a suitable format
            $ratio = $totalInterviewed / $totalAppointed;
            return sprintf('%.2f', $ratio);
        }

        return 'N/A';
    }

    /**
     * Convert a time string in the format 'D H M' or 'H M' or 'M S' to seconds.
     *
     * @param string $time The time string to convert.
     * @return int The total time in seconds.
     */
    private function convertTimeToSeconds(string $time): int
    {
        $totalSeconds = 0;
        preg_match('/(?:(\d+)D)?\s*(?:(\d+)H)?\s*(?:(\d+)M)?\s*(?:(\d+)S)?/', $time, $matches);

        // Extract days, hours, minutes, and seconds
        $days = (int) ($matches[1] ?? 0);
        $hours = (int) ($matches[2] ?? 0);
        $minutes = (int) ($matches[3] ?? 0);
        $seconds = (int) ($matches[4] ?? 0);

        // Convert everything to seconds
        $totalSeconds += $days * 86400;
        $totalSeconds += $hours * 3600;
        $totalSeconds += $minutes * 60;
        $totalSeconds += $seconds;

        return $totalSeconds;
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
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
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
}
