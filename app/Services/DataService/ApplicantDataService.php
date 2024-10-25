<?php

namespace App\Services\DataService;

use Carbon\Carbon;
use App\Models\State;
use App\Models\Vacancy;
use App\Models\Race;
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
    public function getTalentPoolApplicantsDemographic(string $type, ?int $id, Carbon $startDate, Carbon $endDate): array
    {
        // Retrieve the complete state id
        $completeStateID = State::where('code', 'complete')->value('id');
        if (!$completeStateID) {
            return []; // Handle case where 'complete' state does not exist
        }

        // Get the total number of talent pool applicants (state >= complete)
        $totalApplicants = Applicant::whereBetween('created_at', [$startDate, $endDate])
            ->where('state_id', '>=', $completeStateID)
            ->count();

        if ($totalApplicants === 0) {
            return []; // If no applicants, return an empty array
        }

        // Use the Race model to count applicants by race and calculate percentage
        $demographicCounts = Race::withCount(['applicants' => function ($query) use ($startDate, $endDate, $completeStateID) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('state_id', '>=', $completeStateID);
        }])
        ->get()
        ->map(function ($race) use ($totalApplicants) {
            // Calculate percentage for each race
            $percentage = ($race->applicants_count / $totalApplicants) * 100;
            return [
                'name' => $race->name,
                'percentage' => round($percentage)
            ];
        })
        ->toArray();

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
            ->whereHas('interviews', function ($q) {
                $q->whereNotNull('score'); // Only include applicants with a non-null interview score
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
}
