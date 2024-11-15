<?php

namespace App\Services\DataService;

use Carbon\Carbon;
use App\Models\State;
use App\Models\Vacancy;
use App\Models\Store;
use App\Models\Race;
use App\Models\Gender;
use App\Models\Town;
use App\Models\Province;
use App\Models\Applicant;
use Illuminate\Support\Facades\DB;

class ApplicantDataService
{
    /**
     * Calculate the average score for all applicants in the talent pool where the score is not null.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @return float The average score of applicants.
     */
    public function getAverageScoreTalentPoolApplicants(string $type, ?int $id, Carbon $startDate, Carbon $endDate): float
    {
        $applicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('score')
            ->get();

        $totalScore = 0;
        $applicantCount = 0;

        // Sum up the scores of applicants and count them
        foreach ($applicants as $applicant) {
            if ($applicant->score !== null) {
                $totalScore += $applicant->score;
                $applicantCount++;
            }
        }

        // Calculate and return the average score
        if ($applicantCount > 0) {
            return round($totalScore / $applicantCount, 2); // Return the average score rounded to 2 decimal places
        }

        // Return 0 if no applicants with a score are found
        return 0;
    }

    /**
     * Calculate the average score for all appointed applicants in vacancies filtered by store, division, or region.
     *
     * @param string $type The type of filter (e.g., store, division, region).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering vacancies.
     * @param \Carbon\Carbon $endDate The end date for filtering vacancies.
     * @return float The average score of appointed applicants.
     */
    public function getAverageScoreApplicantsAppointed(string $type, ?int $id, Carbon $startDate, Carbon $endDate): float
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
                // Only load appointed applicants with a score
                $query->whereNotNull('score');
            }])
            ->get();

        $totalScore = 0;
        $applicantCount = 0;

        // Loop through each vacancy and its appointed applicants
        foreach ($vacancies as $vacancy) {
            foreach ($vacancy->appointed as $applicant) {
                // Sum up the scores of appointed applicants
                if ($applicant->score !== null) {
                    $totalScore += $applicant->score;
                    $applicantCount++;
                }
            }
        }

        // Calculate the average score
        if ($applicantCount > 0) {
            return round($totalScore / $applicantCount, 2); // Return the average score rounded to 2 decimal places
        } else {
            return 0; // Return 0 if no applicants with a score are found
        }
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
     * Calculate the average literacy score for all applicants in the talent pool where literacy_score is not null.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @return float The average literacy score of applicants.
     */
    public function getAverageLiteracyScoreTalentPoolApplicants(string $type, ?int $id, Carbon $startDate, Carbon $endDate): float
    {
        $applicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('literacy_score')
            ->get();

        $totalScore = 0;
        $applicantCount = 0;

        // Sum up the literacy scores of applicants and count them
        foreach ($applicants as $applicant) {
            if ($applicant->literacy_score !== null) {
                $totalScore += $applicant->literacy_score;
                $applicantCount++;
            }
        }

        // Calculate and return the average literacy score
        if ($applicantCount > 0) {
            return round($totalScore / $applicantCount); // Return the average literacy score rounded to 2 decimal places
        }

        // Return 0 if no applicants with a literacy score are found
        return 0;
    }

    /**
     * Calculate the average numeracy score for all applicants in the talent pool where numeracy_score is not null.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @return float The average numeracy score of applicants.
     */
    public function getAverageNumeracyScoreTalentPoolApplicants(string $type, ?int $id, Carbon $startDate, Carbon $endDate): float
    {
        $applicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('numeracy_score')
            ->get();

        $totalScore = 0;
        $applicantCount = 0;

        // Sum up the numeracy scores of applicants and count them
        foreach ($applicants as $applicant) {
            if ($applicant->numeracy_score !== null) {
                $totalScore += $applicant->numeracy_score;
                $applicantCount++;
            }
        }

        // Calculate and return the average numeracy score
        if ($applicantCount > 0) {
            return round($totalScore / $applicantCount); // Return the average numeracy score rounded to 2 decimal places
        }

        // Return 0 if no applicants with a numeracy score are found
        return 0;
    }

    /**
     * Calculate the average situational score for all applicants in the talent pool where situational_score is not null.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @return float The average situational score of applicants.
     */
    public function getAverageSituationalScoreTalentPoolApplicants(string $type, ?int $id, Carbon $startDate, Carbon $endDate): float
    {
        $applicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('situational_score')
            ->get();

        $totalScore = 0;
        $applicantCount = 0;

        // Sum up the situational scores of applicants and count them
        foreach ($applicants as $applicant) {
            if ($applicant->situational_score !== null) {
                $totalScore += $applicant->situational_score;
                $applicantCount++;
            }
        }

        // Calculate and return the average situational score
        if ($applicantCount > 0) {
            return round($totalScore / $applicantCount); // Return the average situational score rounded to 2 decimal places
        }

        // Return 0 if no applicants with a situational score are found
        return 0;
    }

    /**
     * Get the total number of WhatsApp applicants within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @return int The total count of WhatsApp applicants.
     */
    public function getTotalWhatsappApplicants(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return 0; // Handle case where 'complete' state does not exist
        }

        // Filter applicants based on type, state, and application type (WhatsApp)
        $whatsappApplicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '>=', $completeStateID)
            ->where('application_type', 'WhatsApp')
            ->count();

        return $whatsappApplicants;
    }

    /**
     * Get the total number of Website applicants within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @return int The total count of Website applicants.
     */
    public function getTotalWebsiteApplicants(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return 0; // Handle case where 'complete' state does not exist
        }

        // Filter applicants based on type, state, and application type (Website)
        $websiteApplicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '>=', $completeStateID)
            ->where('application_type', 'Website')
            ->count();

        return $websiteApplicants;
    }

    /**
     * Get the total number of applicants within a given date range, regardless of their state.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @return int The total count of applicants.
     */
    public function getTotalApplicants(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Retrieve the total applicants within the given date range
        $totalApplicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return $totalApplicants;
    }

    /**
     * Get the total number of completed applicants within a given date range (state_id >= completed).
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @return int The total count of completed applicants.
     */
    public function getTotalCompletedApplicants(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return 0; // Handle case where 'complete' state does not exist
        }

        // Filter applicants by date range and state_id >= complete
        $completedApplicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '>=', $completeStateID)
            ->count();

        return $completedApplicants;
    }

    /**
     * Get the most common drop-off state (state_id < completed) within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering.
     * @param \Carbon\Carbon $endDate The end date for filtering.
     * @return string|null The name of the drop-off state.
     */
    public function getDropOffState(string $type, ?int $id, Carbon $startDate, Carbon $endDate): ?string
    {
        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return 'None'; // Handle case where 'complete' state does not exist
        }

        // Query to get the applicants with state_id < complete within the date range
        $dropOffStates = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '<', $completeStateID)
            ->select('state_id', DB::raw('COUNT(state_id) as state_count')) // Count the occurrences of each state
            ->groupBy('state_id')
            ->orderBy('state_count', 'desc') // Sort by occurrences, highest first
            ->orderBy('state_id', 'asc')     // Sort by state_id to pick the smallest in case of tie
            ->first();                       // Get the top result

        // If no drop-off state is found, return null
        if (!$dropOffStates) {
            return 'None';
        }

        // Get the state name from the state_id
        $stateName = State::where('id', $dropOffStates->state_id)->value('name');

        return $stateName; // Return the name of the state
    }

    /**
     * Get the demographic breakdown (in percentage) of talent pool applicants by race within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @return array An array containing the percentage of applicants for each race.
     */
    public function getTalentPoolApplicantsDemographic(string $type, ?int $id, Carbon $startDate, Carbon $endDate, $maxDistanceFromStore): array
    {
        // Retrieve the ID of the "complete" state from the States table
        // This ID will be used to filter applicants based on their state
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return []; // If the "complete" state is not found, return an empty array
        }

        // Initialize an array to store coordinates for stores (used for distance calculation)
        $storeCoordinates = [];

        // If filtering by specific store, division, or region
        if ($type !== 'all') {
            // Retrieve stores based on the specified type and ID (store, division, or region)
            $stores = Store::when($type === 'store', fn($query) => $query->where('id', $id))
                ->when($type === 'division', fn($query) => $query->where('division_id', $id))
                ->when($type === 'region', fn($query) => $query->where('region_id', $id))
                ->get();

            // For each store, extract its coordinates and add to the storeCoordinates array
            foreach ($stores as $store) {
                if ($store->coordinates) {
                    // Split coordinates into latitude and longitude
                    $storeCoordinates[] = explode(',', $store->coordinates);
                }
            }

            // If no valid coordinates are found, return an empty result
            if (empty($storeCoordinates)) {
                return [];
            }
        }

        // Start building the base query for counting applicants in the talent pool
        $applicantQuery = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '>=', $completeStateID); // Only include applicants with a state >= 'complete'

        // Apply distance filtering if specific store(s) are involved
        if ($type !== 'all') {
            // Use the coordinates of each store and apply the distance filter
            $applicantQuery->where(function ($query) use ($storeCoordinates, $maxDistanceFromStore) {
                foreach ($storeCoordinates as [$storeLat, $storeLng]) {
                    // Calculate distance in meters from each store
                    $query->orWhereRaw("
                        ST_Distance_Sphere(
                            point(
                                SUBSTRING_INDEX(applicants.coordinates, ',', -1), 
                                SUBSTRING_INDEX(applicants.coordinates, ',', 1)
                            ), 
                            point(?, ?)
                        ) <= ?
                    ", [floatval($storeLng), floatval($storeLat), $maxDistanceFromStore * 1000]); // maxDistanceFromStore in meters
                }
            });
        }

        // Count the total number of applicants that match the criteria
        $totalApplicants = $applicantQuery->count();

        // If there are no applicants, return an empty array
        if ($totalApplicants === 0) {
            return [];
        }

        // Query the Race model to count applicants by race within the specified criteria
        $demographicCounts = Race::withCount(['applicants' => function ($query) use ($startDate, $endDate, $completeStateID, $storeCoordinates, $maxDistanceFromStore) {
            // Filter applicants by date range and state
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID);

            // Apply the same distance filter if we are querying specific stores
            if (!empty($storeCoordinates)) {
                $query->where(function ($query) use ($storeCoordinates, $maxDistanceFromStore) {
                    foreach ($storeCoordinates as [$storeLat, $storeLng]) {
                        $query->orWhereRaw("
                            ST_Distance_Sphere(
                                point(
                                    SUBSTRING_INDEX(applicants.coordinates, ',', -1), 
                                    SUBSTRING_INDEX(applicants.coordinates, ',', 1)
                                ), 
                                point(?, ?)
                            ) <= ?
                        ", [floatval($storeLng), floatval($storeLat), $maxDistanceFromStore * 1000]);
                    }
                });
            }
        }])
        ->get()
        ->map(function ($race) use ($totalApplicants) {
            // Calculate the percentage for each race based on the total number of applicants
            $percentage = ($race->applicants_count / $totalApplicants) * 100;
            return [
                'name' => $race->name,
                'percentage' => round($percentage) // Round percentage to the nearest integer
            ];
        })
        ->toArray();

        // Return the demographic counts with race names and their percentages
        return $demographicCounts;
    }

    /**
     * Get the demographic breakdown (in percentage) of interviewed applicants by race within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @return array An array containing the percentage of interviewed applicants for each race.
     */
    public function getInterviewedApplicantsDemographic(string $type, ?int $id, Carbon $startDate, Carbon $endDate): array
    {
        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return []; // Handle case where 'complete' state does not exist
        }

        // Get the total number of interviewed applicants
        $totalInterviewedApplicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '>=', $completeStateID)
            ->whereHas('interviews', function ($q) use ($type, $id) {
                $q->whereNotNull('score'); // Only include applicants with a non-null interview score

                 // Apply additional filtering based on type (store, division, or region)
                if ($type === 'store' && $id) {
                    $q->whereHas('vacancy', function ($query) use ($id) {
                        $query->where('store_id', $id);
                    });
                } elseif ($type === 'division' && $id) {
                    $q->whereHas('vacancy.store', function ($query) use ($id) {
                        $query->where('division_id', $id);
                    });
                } elseif ($type === 'region' && $id) {
                    $q->whereHas('vacancy.store', function ($query) use ($id) {
                        $query->where('region_id', $id);
                    });
                }
            })
            ->count();

        if ($totalInterviewedApplicants === 0) {
            return []; // If no applicants, return an empty array
        }

        // Use the Race model to count interviewed applicants by race and calculate percentage
        $demographicCounts = Race::withCount(['applicants' => function ($query) use ($startDate, $endDate, $completeStateID) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID)
                ->whereHas('interviews', function ($q) {
                    $q->whereNotNull('score'); // Only include applicants with a non-null interview score
                });
        }])
        ->get()
        ->map(function ($race) use ($totalInterviewedApplicants) {
            // Calculate percentage for each race
            $percentage = ($race->applicants_count / $totalInterviewedApplicants) * 100;
            return [
                'name' => $race->name,
                'percentage' => round($percentage)
            ];
        })
        ->toArray();

        return $demographicCounts;
    }

    /**
     * Get the demographic breakdown (in percentage) of appointed applicants by race within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @return array An array containing the percentage of appointed applicants for each race.
     */
    public function getAppointedApplicantsDemographic(string $type, ?int $id, Carbon $startDate, Carbon $endDate): array
    {
        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return []; // Handle case where 'complete' state does not exist
        }

        // Get the total number of appointed applicants
        $totalAppointedApplicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '>=', $completeStateID)
            ->whereNotNull('appointed_id') // Only include applicants with an appointed_id
            ->whereHas('vacanciesFilled', function ($q) use ($type, $id) {
                // Apply additional filtering based on type (store, division, or region)
                if ($type === 'store' && $id) {
                    $q->where('store_id', $id);
                } elseif ($type === 'division' && $id) {
                    $q->whereHas('store', function ($query) use ($id) {
                        $query->where('division_id', $id);
                    });
                } elseif ($type === 'region' && $id) {
                    $q->whereHas('store', function ($query) use ($id) {
                        $query->where('region_id', $id);
                    });
                }
            })
            ->count();

        if ($totalAppointedApplicants === 0) {
            return []; // If no applicants, return an empty array
        }

        // Use the Race model to count appointed applicants by race and calculate percentage
        $demographicCounts = Race::withCount(['applicants' => function ($query) use ($startDate, $endDate, $completeStateID) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID)
                ->whereNotNull('appointed_id'); // Only include applicants with an appointed_id
        }])
        ->get()
        ->map(function ($race) use ($totalAppointedApplicants) {
            // Calculate percentage for each race
            $percentage = ($race->applicants_count / $totalAppointedApplicants) * 100;
            return [
                'name' => $race->name,
                'percentage' => round($percentage)
            ];
        })
        ->toArray();

        return $demographicCounts;
    }

    /**
     * Get the demographic breakdown (in percentage) of talent pool applicants by gender within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter (null for 'all').
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @param float $maxDistanceFromStore The maximum distance (in kilometers) from the store to consider applicants.
     * @return array An array containing the percentage breakdown of applicants for each gender.
     */
    public function getTalentPoolApplicantsGender(string $type, ?int $id, Carbon $startDate, Carbon $endDate, float $maxDistanceFromStore): array
    {
        // Retrieve the ID of the "complete" state from the States table
        // This ID is used to filter applicants who are in a "complete" or later state
        $completeStateID = State::where('code', 'complete')->value('id');

        // If the "complete" state is not found, return an empty array
        if (!$completeStateID) {
            return [];
        }

        // Initialize an array to hold the coordinates of stores for distance calculations
        $storeCoordinates = [];

        // Check if filtering by specific store, division, or region
        if ($type !== 'all') {
            // Retrieve stores based on the specified filter type and ID
            // (e.g., if $type is 'store', it retrieves the store with the given $id)
            $stores = Store::when($type === 'store', fn($query) => $query->where('id', $id))
                ->when($type === 'division', fn($query) => $query->where('division_id', $id))
                ->when($type === 'region', fn($query) => $query->where('region_id', $id))
                ->get();

            // Loop through each store and extract its coordinates
            foreach ($stores as $store) {
                if ($store->coordinates) {
                    // Split coordinates into latitude and longitude and add to the array
                    $storeCoordinates[] = explode(',', $store->coordinates);
                }
            }

            // If no valid coordinates are found for the specified filter, return an empty array
            if (empty($storeCoordinates)) {
                return [];
            }
        }

        // Initialize the base query to retrieve applicants in the specified date range with "complete" or higher state
        $applicantQuery = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '>=', $completeStateID);

        // Apply distance filtering if a specific store, division, or region is specified
        if ($type !== 'all') {
            // Filter applicants by distance from each store in the coordinates array
            $applicantQuery->where(function ($query) use ($storeCoordinates, $maxDistanceFromStore) {
                foreach ($storeCoordinates as [$storeLat, $storeLng]) {
                    // Calculate distance in meters from each store coordinate
                    $query->orWhereRaw("
                        ST_Distance_Sphere(
                            point(
                                SUBSTRING_INDEX(applicants.coordinates, ',', -1), 
                                SUBSTRING_INDEX(applicants.coordinates, ',', 1)
                            ), 
                            point(?, ?)
                        ) <= ?
                    ", [floatval($storeLng), floatval($storeLat), $maxDistanceFromStore * 1000]); // Convert maxDistanceFromStore to meters
                }
            });
        }

        // Count the total number of applicants that match the criteria
        $totalApplicants = $applicantQuery->count();

        // If there are no applicants, return an empty array
        if ($totalApplicants === 0) {
            return [];
        }

        // Retrieve the gender breakdown by counting applicants for each gender
        $genderCounts = Gender::withCount(['applicants' => function ($query) use ($startDate, $endDate, $completeStateID, $storeCoordinates, $maxDistanceFromStore) {
            // Filter applicants by date range and "complete" state
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID);

            // Apply the same distance filter if specific stores are involved
            if (!empty($storeCoordinates)) {
                $query->where(function ($query) use ($storeCoordinates, $maxDistanceFromStore) {
                    foreach ($storeCoordinates as [$storeLat, $storeLng]) {
                        // Calculate distance in meters from each store coordinate
                        $query->orWhereRaw("
                            ST_Distance_Sphere(
                                point(
                                    SUBSTRING_INDEX(applicants.coordinates, ',', -1), 
                                    SUBSTRING_INDEX(applicants.coordinates, ',', 1)
                                ), 
                                point(?, ?)
                            ) <= ?
                        ", [floatval($storeLng), floatval($storeLat), $maxDistanceFromStore * 1000]);
                    }
                });
            }
        }])
        ->get()
        ->map(function ($gender) use ($totalApplicants) {
            // Calculate the percentage of applicants for each gender
            $percentage = ($gender->applicants_count / $totalApplicants) * 100;
            return [
                'name' => $gender->name,
                'percentage' => round($percentage) // Round percentage to the nearest integer
            ];
        })
        ->toArray();

        // Return the gender breakdown with percentages
        return $genderCounts;
    }

    /**
     * Get the demographic breakdown (in percentage) of interviewed applicants by gender within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter (null for 'all').
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @return array An array containing the percentage of interviewed applicants for each gender.
     */
    public function getInterviewedApplicantsGender(string $type, ?int $id, Carbon $startDate, Carbon $endDate): array
    {
        // Retrieve the ID of the "complete" state from the States table
        // This ID is used to filter applicants who are in a "complete" or later state
        $completeStateID = State::where('code', 'complete')->value('id');

        // If the "complete" state is not found, return an empty array
        if (!$completeStateID) {
            return [];
        }

        // Get the total number of interviewed applicants that match the criteria
        $totalInterviewedApplicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '>=', $completeStateID)
            ->whereHas('interviews', function ($q) use ($type, $id) {
                $q->whereNotNull('score'); // Only include applicants with a non-null interview score

                // Apply additional filtering based on type (store, division, or region)
                if ($type === 'store' && $id) {
                    $q->whereHas('vacancy', function ($query) use ($id) {
                        $query->where('store_id', $id);
                    });
                } elseif ($type === 'division' && $id) {
                    $q->whereHas('vacancy.store', function ($query) use ($id) {
                        $query->where('division_id', $id);
                    });
                } elseif ($type === 'region' && $id) {
                    $q->whereHas('vacancy.store', function ($query) use ($id) {
                        $query->where('region_id', $id);
                    });
                }
            })
            ->count();

        // If there are no interviewed applicants, return an empty array
        if ($totalInterviewedApplicants === 0) {
            return [];
        }

        // Use the Gender model to count interviewed applicants by gender and calculate percentages
        $genderCounts = Gender::withCount(['applicants' => function ($query) use ($startDate, $endDate, $completeStateID) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID)
                ->whereHas('interviews', function ($q) {
                    $q->whereNotNull('score'); // Only include applicants with a non-null interview score
                });
        }])
        ->get()
        ->map(function ($gender) use ($totalInterviewedApplicants) {
            // Calculate the percentage for each gender based on the total number of interviewed applicants
            $percentage = ($gender->applicants_count / $totalInterviewedApplicants) * 100;
            return [
                'name' => $gender->name,
                'percentage' => round($percentage) // Round percentage to the nearest integer
            ];
        })
        ->toArray();

        // Return the gender breakdown with percentages
        return $genderCounts;
    }

    /**
     * Get the demographic breakdown (in percentage) of appointed applicants by gender within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter (null for 'all').
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @return array An array containing the percentage of appointed applicants for each gender.
     */
    public function getAppointedApplicantsGender(string $type, ?int $id, Carbon $startDate, Carbon $endDate): array
    {
        // Retrieve the ID of the "complete" state from the States table
        // This ID is used to filter applicants who are in a "complete" or later state
        $completeStateID = State::where('code', 'complete')->value('id');

        // If the "complete" state is not found, return an empty array
        if (!$completeStateID) {
            return [];
        }

        // Get the total number of appointed applicants that match the criteria
        $totalAppointedApplicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '>=', $completeStateID)
            ->whereNotNull('appointed_id') // Only include applicants with an appointed_id
            ->whereHas('vacanciesFilled', function ($q) use ($type, $id) {
                // Apply additional filtering based on type (store, division, or region)
                if ($type === 'store' && $id) {
                    $q->where('store_id', $id);
                } elseif ($type === 'division' && $id) {
                    $q->whereHas('store', function ($query) use ($id) {
                        $query->where('division_id', $id);
                    });
                } elseif ($type === 'region' && $id) {
                    $q->whereHas('store', function ($query) use ($id) {
                        $query->where('region_id', $id);
                    });
                }
            })
            ->count();

        // If there are no appointed applicants, return an empty array
        if ($totalAppointedApplicants === 0) {
            return [];
        }

        // Use the Gender model to count appointed applicants by gender and calculate percentages
        $genderCounts = Gender::withCount(['applicants' => function ($query) use ($startDate, $endDate, $completeStateID) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID)
                ->whereNotNull('appointed_id'); // Only include applicants with an appointed_id
        }])
        ->get()
        ->map(function ($gender) use ($totalAppointedApplicants) {
            // Calculate the percentage for each gender based on the total number of appointed applicants
            $percentage = ($gender->applicants_count / $totalAppointedApplicants) * 100;
            return [
                'name' => $gender->name,
                'percentage' => round($percentage) // Round percentage to the nearest integer
            ];
        })
        ->toArray();

        // Return the gender breakdown with percentages
        return $genderCounts;
    }

    /**
     * Get the demographic breakdown of talent pool applicants by province within a given date range.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter.
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @return array An array containing the count of applicants for each province.
     */
    public function getTalentPoolApplicantsProvince(string $type, ?int $id, Carbon $startDate, Carbon $endDate): array
    {
        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return []; // Handle case where 'complete' state does not exist
        }

        // Use the Province model to count applicants by province
        $provinceCounts = Province::withCount(['applicants' => function ($query) use ($startDate, $endDate, $completeStateID) {
            // Filter applicants by the date range and completed state
            $query->whereBetween('applicants.created_at', [$startDate, $endDate])  // Specify 'applicants' table for created_at
                ->where('applicants.state_id', '>=', $completeStateID);
        }])
        ->get()
        ->pluck('applicants_count', 'name') // Map province names to their applicant counts
        ->toArray();

        return $provinceCounts;
    }

    /**
     * Get the total number of re-employed applicants.
     *
     * Re-employed applicants are defined as applicants with a non-null `appointed_id`
     * and `employment` status equal to 'P'.
     *
     * @param string $type The type of filter (e.g., store, division, region, or all).
     * @param int|null $id The ID of the store, division, or region to filter (null for 'all').
     * @param \Carbon\Carbon $startDate The start date for filtering applicants.
     * @param \Carbon\Carbon $endDate The end date for filtering applicants.
     * @return int The total count of re-employed applicants.
     */
    public function getTotalReEmployedApplicants(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Initialize the base query to filter applicants within the date range,
        // where `appointed_id` is not null and `employment` status is 'P'
        $query = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('appointed_id')
            ->where('employment', 'P');

        // Return the count of re-employed applicants
        return $query->count();
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
    public function getTotalAppointedApplicants(string $type, ?int $id, Carbon $startDate, Carbon $endDate): int
    {
        // Initialize the base query to filter applicants within the date range,
        // where `appointed_id` is not null
        $query = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('appointed_id');

        // Return the count of appointed applicants
        return $query->count();
    }
}
