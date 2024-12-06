<?php

namespace App\Services\DataService\Reports;

use Carbon\Carbon;
use App\Models\State;
use App\Models\Store;
use App\Models\Vacancy;
use App\Models\Applicant;
use App\Models\Interview;
use Illuminate\Support\Facades\DB;

class StoresReportDataService
{
    /**
     * Calculate the average time to shortlist for within a date range.
     *
     * @param string $type The type of view (e.g., national, division, area, store).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return string
     */
    public function getAverageTimeToShortlist(string $type = null, ?int $id = null, Carbon $startDate, Carbon $endDate): string
    {
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
     * Calculate the average time to hire for a specific type within a date range.
     *
     * @param string $type The type of view (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering vacancies.
     * @param \Carbon\Carbon $endDate The end date for filtering vacancies.
     * @return string The average time to hire in the format 'D H M' (Days, Hours, Minutes).
     */
    public function getAverageTimeToHire(string $type = null, ?int $id = null, Carbon $startDate, Carbon $endDate): string
    {
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
            ->with(['appointed' => function ($query) {
                $query->orderBy('vacancy_fills.created_at', 'asc'); // Order by first appointment
            }])
            ->get();

        $totalTimeInSeconds = 0;
        $hiringCount = 0;

        // Loop through each vacancy and calculate the time difference for the first appointed record
        foreach ($vacancies as $vacancy) {
            if ($vacancy->appointed->isNotEmpty()) {
                // Get the first appointment record
                $firstAppointed = $vacancy->appointed->first();

                // Calculate the time difference between vacancy creation and the first appointment in seconds
                $totalTimeInSeconds += $vacancy->created_at->diffInSeconds($firstAppointed->pivot->created_at);
                $hiringCount++;
            }
        }

        // Calculate the average time to hire
        if ($hiringCount > 0) {
            $averageTimeInSeconds = $totalTimeInSeconds / $hiringCount;

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

        // Return default format if no appointments
        return '0D 0H 0M';
    }

    /**
     * Get the total number of appointed applicants.
     *
     * Appointed applicants are defined as applicants with a non-null `appointed_id`.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter (null for 'all').
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @return int The total count of appointed applicants.
     */
    public function getTotalApplicantsAppointed(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Use a query that joins with the `vacancy_fills` table to filter by `appointed_created_at`.
        $applicants = Applicant::whereHas('vacancyFill', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]);
        });

        // Apply additional filters based on the $type and $id if needed
        if ($type !== 'all') {
            // For example, filter by store, division, or region
            $applicants->whereHas('vacancyFill.vacancy', function ($query) use ($type, $id) {
                if ($type === 'store') {
                    $query->where('store_id', $id);
                } elseif ($type === 'division') {
                    $query->whereHas('store', function ($query) use ($id) {
                        $query->where('division_id', $id);
                    });
                } elseif ($type === 'region') {
                    $query->whereHas('store', function ($query) use ($id) {
                        $query->where('region_id', $id);
                    });
                }
            });
        }

        // Return the count of appointed applicants
        return $applicants->count();
    }

    /**
     * Count the number of talent pool applicants within a given distance from the store, division, or region, or all applicants if type is 'all'.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param float $maxDistanceFromStore The maximum distance from the store in kilometers.
     * @return int The count of talent pool applicants within the given distance or all applicants if type is 'all'.
     */
    public function getTalentPoolApplicants(string $type, ?int $id, $startDate, $endDate, $maxDistanceFromStore): int
    {
        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return 0; // Handle case where 'complete' state does not exist
        }

        // Check if the type is 'all' to get all applicants within the date range
        if ($type === 'all') {
            return Applicant::whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID)
                ->count(); // Simply return all applicants within the date range, ignoring distance
        }

        // Otherwise, proceed with filtering by store, division, or region
        $stores = Store::when($type === 'store', function ($query) use ($id) {
                return $query->where('id', $id);
            })
            ->when($type === 'division', function ($query) use ($id) {
                return $query->where('division_id', $id);
            })
            ->when($type === 'region', function ($query) use ($id) {
                return $query->where('region_id', $id);
            })
            ->get();

        if ($stores->isEmpty()) {
            return 0; // Return 0 if no stores are found for the given filter
        }

        $applicantCount = 0;

        // Loop through each store and calculate the applicants within the given distance
        foreach ($stores as $store) {
            if ($store->coordinates) {
                $storeCoordinates = explode(',', $store->coordinates);
                $storeLat = floatval($storeCoordinates[0]);
                $storeLng = floatval($storeCoordinates[1]);

                // Count the applicants within the distance range using MySQL ST_Distance_Sphere
                $storeApplicantCount = Applicant::whereBetween('created_at', [$startDate, $endDate])
                    ->where('state_id', '>=', $completeStateID)
                    ->whereRaw("
                        ST_Distance_Sphere(
                            point(
                                SUBSTRING_INDEX(applicants.coordinates, ',', -1), 
                                SUBSTRING_INDEX(applicants.coordinates, ',', 1)
                            ), 
                            point(?, ?)
                        ) <= ?
                    ", [$storeLng, $storeLat, $maxDistanceFromStore * 1000]) // Multiply by 1000 to convert km to meters
                    ->count();

                $applicantCount += $storeApplicantCount;
            }
        }

        return $applicantCount;
    }

    /**
     * Get the total number of applicants saved by users within a date range.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter (null for 'all').
     * @param \Carbon\Carbon $startDate The start date for filtering saved applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering saved applicants.
     * @return int The total count of saved applicants.
     */
    public function getTotalApplicantsSaved(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Base query to count applicants saved by users
        $applicants = Applicant::whereHas('savedBy', function ($query) use ($type, $id, $startDate, $endDate) {
            // Filter by the `created_at` field in the pivot table
            $query->whereBetween('applicant_save.created_at', [$startDate, $endDate]);

            // Apply additional filters based on type
            if ($type !== 'all') {
                $query->whereHas('store', function ($q) use ($type, $id) {
                    if ($type === 'store') {
                        $q->where('id', $id); // Assuming `store_id` refers to `id` in the Store model
                    } elseif ($type === 'region') {
                        $q->where('region_id', $id);
                    } elseif ($type === 'division') {
                        $q->where('division_id', $id);
                    }
                });
            }
        });

        // Return the count of saved applicants
        return $applicants->count();
    }

    /**
     * Calculate the average distance between stores' coordinates and appointed applicants' coordinates for store, division, or region.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @return float The average distance in kilometers.
     */
    public function getAverageDistanceApplicantsAppointed(string $type, ?int $id, $startDate, $endDate): float
    {
        $totalDistance = 0;
        $applicantCount = 0;

        // Retrieve vacancies and stores based on the type (store, division, region) and date range
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
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['store', 'appointed']) // Load store and appointed applicants relationships
            ->get();

        // Loop through each vacancy and calculate the distance for all appointed applicants
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

        // Calculate the average distance and round it to 1 decimal place
        if ($applicantCount > 0) {
            return round($totalDistance / $applicantCount, 1);
        } else {
            return 0; // Return 0 if no appointed applicants are found
        }
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

    /**
     * Calculate the average assessment score (percentage) for all appointed applicants in vacancies filtered by store, division, or region.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering vacancies.
     * @param \Carbon\Carbon $endDate The end date for filtering vacancies.
     * @return float The average assessment score percentage of appointed applicants.
     */
    public function getAverageAssessmentScoreApplicantsAppointed(string $type, ?int $id, Carbon $startDate, Carbon $endDate): float
    {
        // Retrieve vacancies based on the type (store, division, or region) and within the date range
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
            ->with(['appointed' => function ($query) {
                // Only load appointed applicants with literacy, numeracy, and situational scores and questions
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
        if ($applicantCount > 0) {
            return round($totalAssessmentPercentage / $applicantCount); // Return the average percentage rounded to 2 decimal places
        } else {
            return 0; // Return 0 if no applicants with scores are found
        }
    }

    /**
     * Get Total Interviews Scheduled for a specific type within a date range.
     *
     * @param string $type The type of view (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering interviews.
     * @param \Carbon\Carbon $endDate The end date for filtering interviews.
     * @return int The total count of scheduled interviews.
     */
    public function getTotalInterviewsScheduled(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Start building the query using the Interview model and filter by date range
        $interviews = Interview::whereBetween('created_at', [$startDate, $endDate]);

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

        // Return the total count of scheduled interviews
        return $interviews->count();
    }

    /**
     * Get total number of completed interviews for a specific type within a date range.
     *
     * @param string $type The type of view (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering interviews.
     * @param \Carbon\Carbon $endDate The end date for filtering interviews.
     * @return int The total count of completed interviews.
     */
    public function getTotalInterviewsCompleted(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
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
     * Calculate the filtered average time to shortlist for within a date range.
     *
     * @param string $type The type of view (e.g., national, division, area, store).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @param array $filters An array of additional filters.
     * @return string
     */
    public function getAverageTimeToShortlistFiltered(string $type = null, ?int $id = null, Carbon $startDate, Carbon $endDate, array $filters): string
    {
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
            ->whereHas('store', function ($query) use ($filters) {
                $query->when(isset($filters['brand_id']), function ($q) use ($filters) {
                    $q->where('brand_id', $filters['brand_id']);
                })
                ->when(isset($filters['province_id']), function ($q) use ($filters) {
                    $q->whereHas('town', function ($subQuery) use ($filters) {
                        $subQuery->where('province_id', $filters['province_id']);
                    });
                })
                ->when(isset($filters['town_id']), function ($q) use ($filters) {
                    $q->where('town_id', $filters['town_id']);
                })
                ->when(isset($filters['store_id']), function ($q) use ($filters) {
                    if (is_array($filters['store_id'])) {
                        $q->whereIn('id', $filters['store_id']);
                    }
                })
                ->when(isset($filters['division_id']), function ($q) use ($filters) {
                    $q->where('division_id', $filters['division_id']);
                })
                ->when(isset($filters['region_id']), function ($q) use ($filters) {
                    $q->where('region_id', $filters['region_id']);
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
     * Calculate the filtered average time to hire for a specific type within a date range.
     *
     * @param string $type The type of view (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering vacancies.
     * @param \Carbon\Carbon $endDate The end date for filtering vacancies.
     * @param array $filters An array of additional filters.
     * @return string The average time to hire in the format 'D H M' (Days, Hours, Minutes).
     */
    public function getAverageTimeToHireFiltered(string $type = null, ?int $id = null, Carbon $startDate, Carbon $endDate, array $filters): string
    {
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
            ->whereHas('store', function ($query) use ($filters) {
                $query->when(isset($filters['brand_id']), function ($q) use ($filters) {
                    $q->where('brand_id', $filters['brand_id']);
                })
                ->when(isset($filters['province_id']), function ($q) use ($filters) {
                    $q->whereHas('town', function ($subQuery) use ($filters) {
                        $subQuery->where('province_id', $filters['province_id']);
                    });
                })
                ->when(isset($filters['town_id']), function ($q) use ($filters) {
                    $q->where('town_id', $filters['town_id']);
                })
                ->when(isset($filters['store_id']), function ($q) use ($filters) {
                    if (is_array($filters['store_id'])) {
                        $q->whereIn('id', $filters['store_id']);
                    }
                })
                ->when(isset($filters['division_id']), function ($q) use ($filters) {
                    $q->where('division_id', $filters['division_id']);
                })
                ->when(isset($filters['region_id']), function ($q) use ($filters) {
                    $q->where('region_id', $filters['region_id']);
                });
            })
            ->with(['appointed' => function ($query) {
                $query->orderBy('vacancy_fills.created_at', 'asc'); // Order by first appointment
            }])
            ->get();

        $totalTimeInSeconds = 0;
        $hiringCount = 0;

        // Loop through each vacancy and calculate the time difference for the first appointed record
        foreach ($vacancies as $vacancy) {
            if ($vacancy->appointed->isNotEmpty()) {
                // Get the first appointment record
                $firstAppointed = $vacancy->appointed->first();

                // Calculate the time difference between vacancy creation and the first appointment in seconds
                $totalTimeInSeconds += $vacancy->created_at->diffInSeconds($firstAppointed->pivot->created_at);
                $hiringCount++;
            }
        }

        // Calculate the average time to hire
        if ($hiringCount > 0) {
            $averageTimeInSeconds = $totalTimeInSeconds / $hiringCount;

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

        // Return default format if no appointments
        return '0D 0H 0M';
    }

    /**
     * Get the total number of appointed applicants with additional filters.
     *
     * Appointed applicants are defined as applicants with a non-null `appointed_id`.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter (null for 'all').
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @param array $filters An array of additional filters.
     * @return int The total count of appointed applicants.
     */
    public function getTotalApplicantsAppointedFiltered(string $type, ?int $id, Carbon $startDate, Carbon $endDate, array $filters): int
    {
        // Use a query that joins with the `vacancy_fills` table to filter by `appointed_created_at`.
        $applicants = Applicant::whereHas('vacancyFill', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('vacancy_fills.created_at', [$startDate, $endDate]);
        });

        // Apply additional filters based on the $type and $id
        if ($type !== 'all') {
            $applicants->whereHas('vacancyFill.vacancy', function ($query) use ($type, $id) {
                if ($type === 'store') {
                    $query->where('store_id', $id);
                } elseif ($type === 'division') {
                    $query->where('division_id', $id);
                } elseif ($type === 'region') {
                    $query->where('region_id', $id);
                }
            });
        }

        // Apply additional filters
        if (isset($filters['brand_id'])) {
            $applicants->whereHas('vacancyFill.vacancy.store', function ($query) use ($filters) {
                $query->where('brand_id', $filters['brand_id']);
            });
        }

        if (isset($filters['province_id'])) {
            $applicants->whereHas('vacancyFill.vacancy.store.town', function ($query) use ($filters) {
                $query->where('province_id', $filters['province_id']);
            });
        }

        if (isset($filters['town_id'])) {
            $applicants->whereHas('vacancyFill.vacancy.store', function ($query) use ($filters) {
                $query->where('town_id', $filters['town_id']);
            });
        }

        if (isset($filters['store_id'])) {
            $applicants->whereHas('vacancyFill.vacancy', function ($query) use ($filters) {
                if (is_array($filters['store_id'])) {
                    $query->whereIn('store_id', $filters['store_id']);
                }
            });
        }

        if (isset($filters['division_id'])) {
            $applicants->whereHas('vacancyFill.vacancy.store', function ($query) use ($filters) {
                $query->where('division_id', $filters['division_id']);
            });
        }

        if (isset($filters['region_id'])) {
            $applicants->whereHas('vacancyFill.vacancy.store', function ($query) use ($filters) {
                $query->where('region_id', $filters['region_id']);
            });
        }

        // Return the count of appointed applicants
        return $applicants->count();
    }

    /**
     * Get the total number of applicants saved by users within a date range with additional filters.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter (null for 'all').
     * @param \Carbon\Carbon $startDate The start date for filtering saved applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering saved applicants.
     * @param array $filters An array of additional filters.
     * @return int The total count of saved applicants.
     */
    public function getTotalApplicantsSavedFiltered(string $type, ?int $id, Carbon $startDate, Carbon $endDate, array $filters): int
    {
        // Base query to count applicants saved by users
        $applicants = Applicant::whereHas('savedBy', function ($query) use ($type, $id, $startDate, $endDate, $filters) {
            // Filter by the `created_at` field in the pivot table
            $query->whereBetween('applicant_save.created_at', [$startDate, $endDate]);

            // Apply additional filters based on type
            if ($type !== 'all') {
                $query->whereHas('store', function ($q) use ($type, $id) {
                    if ($type === 'store') {
                        $q->where('id', $id); // Assuming `store_id` refers to `id` in the Store model
                    } elseif ($type === 'region') {
                        $q->where('region_id', $id);
                    } elseif ($type === 'division') {
                        $q->where('division_id', $id);
                    }
                });
            }

            // Apply additional filters
            if (isset($filters['brand_id'])) {
                $query->whereHas('store', function ($q) use ($filters) {
                    $q->where('brand_id', $filters['brand_id']);
                });
            }
            if (isset($filters['province_id'])) {
                $query->whereHas('store.town', function ($q) use ($filters) {
                    $q->where('province_id', $filters['province_id']);
                });
            }
            if (isset($filters['town_id'])) {
                $query->whereHas('store', function ($q) use ($filters) {
                    $q->where('town_id', $filters['town_id']);
                });
            }
            if (isset($filters['division_id'])) {
                $query->whereHas('store', function ($q) use ($filters) {
                    $q->where('division_id', $filters['division_id']);
                });
            }
            if (isset($filters['region_id'])) {
                $query->whereHas('store', function ($q) use ($filters) {
                    $q->where('region_id', $filters['region_id']);
                });
            }
            if (isset($filters['store_id'])) {
                $query->whereHas('store', function ($q) use ($filters) {
                    if (is_array($filters['store_id'])) {
                        $q->whereIn('id', $filters['store_id']);
                    }
                });
            }
        });

        // Return the count of saved applicants
        return $applicants->count();
    }

    /**
     * Calculate the average distance between stores' coordinates and appointed applicants' coordinates for store, division, or region with additional filters.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @param array $filters An array of additional filters.
     * @return float The average distance in kilometers.
     */
    public function getAverageDistanceApplicantsAppointedFiltered(string $type, ?int $id, $startDate, $endDate, array $filters): float
    {
        $totalDistance = 0;
        $applicantCount = 0;

        // Retrieve vacancies and stores based on the type and date range
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
            ->when(isset($filters['brand_id']), function ($query) use ($filters) {
                return $query->whereHas('store', function ($q) use ($filters) {
                    $q->where('brand_id', $filters['brand_id']);
                });
            })
            ->when(isset($filters['province_id']), function ($query) use ($filters) {
                return $query->whereHas('store.town', function ($q) use ($filters) {
                    $q->where('province_id', $filters['province_id']);
                });
            })
            ->when(isset($filters['town_id']), function ($query) use ($filters) {
                return $query->whereHas('store', function ($q) use ($filters) {
                    $q->where('town_id', $filters['town_id']);
                });
            })
            ->when(isset($filters['division_id']), function ($query) use ($filters) {
                return $query->whereHas('store', function ($q) use ($filters) {
                    $q->where('division_id', $filters['division_id']);
                });
            })
            ->when(isset($filters['region_id']), function ($query) use ($filters) {
                return $query->whereHas('store', function ($q) use ($filters) {
                    $q->where('region_id', $filters['region_id']);
                });
            })
            ->when(isset($filters['store_id']), function ($query) use ($filters) {
                if (is_array($filters['store_id'])) {
                    return $query->whereIn('store_id', $filters['store_id']);
                }
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['store', 'appointed']) // Load store and appointed applicants relationships
            ->get();

        // Loop through each vacancy and calculate the distance for all appointed applicants
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

        // Calculate the average distance and round it to 1 decimal place
        if ($applicantCount > 0) {
            return round($totalDistance / $applicantCount, 1);
        } else {
            return 0; // Return 0 if no appointed applicants are found
        }
    }

    /**
     * Calculate the average assessment score (percentage) for all appointed applicants in vacancies filtered by additional criteria.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering vacancies.
     * @param \Carbon\Carbon $endDate The end date for filtering vacancies.
     * @param array $filters An array of additional filters.
     * @return float The average assessment score percentage of appointed applicants.
     */
    public function getAverageAssessmentScoreApplicantsAppointedFiltered(string $type, ?int $id, Carbon $startDate, Carbon $endDate, array $filters): float
    {
        // Retrieve vacancies based on the type, date range, and additional filters
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
            ->when(isset($filters['brand_id']), function ($query) use ($filters) {
                return $query->whereHas('store', function ($q) use ($filters) {
                    $q->where('brand_id', $filters['brand_id']);
                });
            })
            ->when(isset($filters['province_id']), function ($query) use ($filters) {
                return $query->whereHas('store.town', function ($q) use ($filters) {
                    $q->where('province_id', $filters['province_id']);
                });
            })
            ->when(isset($filters['division_id']), function ($query) use ($filters) {
                return $query->whereHas('store', function ($q) use ($filters) {
                    $q->where('division_id', $filters['division_id']);
                });
            })
            ->when(isset($filters['region_id']), function ($query) use ($filters) {
                return $query->whereHas('store', function ($q) use ($filters) {
                    $q->where('region_id', $filters['region_id']);
                });
            })
            ->when(isset($filters['town_id']), function ($query) use ($filters) {
                return $query->whereHas('store', function ($q) use ($filters) {
                    $q->where('town_id', $filters['town_id']);
                });
            })
            ->when(isset($filters['store_id']), function ($query) use ($filters) {
                if (is_array($filters['store_id'])) {
                    return $query->whereIn('store_id', $filters['store_id']);
                }
            })
            ->with(['appointed' => function ($query) {
                // Only load appointed applicants with literacy, numeracy, and situational scores and questions
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
        if ($applicantCount > 0) {
            return round($totalAssessmentPercentage / $applicantCount); // Return the average percentage rounded to 2 decimal places
        } else {
            return 0; // Return 0 if no applicants with scores are found
        }
    }

    /**
     * Get total number of completed interviews for a specific type within a date range with additional filters.
     *
     * @param string $type The type of view (e.g., store, division, region).
     * @param int|null $id The ID for filtering based on the type.
     * @param \Carbon\Carbon $startDate The start date for filtering interviews.
     * @param \Carbon\Carbon $endDate The end date for filtering interviews.
     * @param array $filters An array of additional filters.
     * @return int The total count of completed interviews.
     */
    public function getTotalInterviewsCompletedFiltered(string $type, ?int $id, Carbon $startDate, Carbon $endDate, array $filters): int
    {
        // Start building the query using the Interview model, filter for completed interviews (score is not null) and date range
        $interviews = Interview::whereNotNull('score')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Filter by type (store, division, region)
        if ($type === 'store') {
            $interviews->whereHas('vacancy', function ($query) use ($id) {
                $query->where('store_id', $id); // Directly filter by store_id in vacancies table
            });
        } elseif ($type === 'division') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('division_id', $id); // Filter by division_id in stores table
            });
        } elseif ($type === 'region') {
            $interviews->whereHas('vacancy.store', function ($query) use ($id) {
                $query->where('region_id', $id); // Filter by region_id in stores table
            });
        }

        // Apply additional filters
        if (isset($filters['brand_id'])) {
            $interviews->whereHas('vacancy.store', function ($query) use ($filters) {
                $query->where('brand_id', $filters['brand_id']);
            });
        }

        if (isset($filters['province_id'])) {
            $interviews->whereHas('vacancy.store.town', function ($query) use ($filters) {
                $query->where('province_id', $filters['province_id']);
            });
        }

        if (isset($filters['division_id'])) {
            $interviews->whereHas('vacancy.store', function ($query) use ($filters) {
                $query->where('division_id', $filters['division_id']);
            });
        }

        if (isset($filters['region_id'])) {
            $interviews->whereHas('vacancy.store', function ($query) use ($filters) {
                $query->where('region_id', $filters['region_id']);
            });
        }

        if (isset($filters['town_id'])) {
            $interviews->whereHas('vacancy.store', function ($query) use ($filters) {
                $query->where('town_id', $filters['town_id']);
            });
        }

        if (isset($filters['store_id'])) {
            $interviews->whereHas('vacancy', function ($query) use ($filters) {
                if (is_array($filters['store_id'])) {
                    $query->whereIn('store_id', $filters['store_id']);
                }
            });
        }
        

        // Return the total count of completed interviews
        return $interviews->count();
    }
}
