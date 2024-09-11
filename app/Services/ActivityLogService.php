<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    /**
     * Query the activity log, filtering for activities related to the allowed models.
     * @param int $authUserId
     * @param array $authVacancyIds
     * @return Collection
     */
    public function getActivityLog(int $authUserId, array $authVacancyIds): Collection
    {
        $allowedModels = $this->allowedModels();

        $activities = Activity::whereIn('subject_type', $allowedModels)
            ->where(function ($query) use ($authUserId, $authVacancyIds) {
                // Filter for activities where the 'causer' (the user who performed the action) is the authenticated user,
                // and the action is one of 'created', 'updated', or 'deleted'.
                $query->where('causer_id', $authUserId)
                    ->whereIn('event', ['created', 'updated', 'deleted']);
            })
            ->orWhere(function ($q) use ($authUserId) {
                // Include activities where the event is 'accessed' (e.g., a user viewed a vacancy or applicant profile),
                // specifically for the authenticated user.
                $q->where('event', 'accessed')
                    ->whereIn('description', ['job-overview.index', 'applicant-profile.index'])
                    ->where('causer_id', $authUserId);
            })
            ->orWhere(function ($q) use ($authUserId) {
                // Include activities related to messages where the authenticated user is the recipient ('to_id').
                $q->where('subject_type', 'App\Models\Message')
                    ->where('properties->attributes->to_id', $authUserId)
                    ->where('event', 'created');
            })
            ->orWhere(function ($q) use ($authVacancyIds) {
                // Include activities related to applications connected to any of the vacancies owned by the authenticated user.
                $q->where('subject_type', 'App\Models\Application')
                    ->whereIn('properties->attributes->vacancy_id', $authVacancyIds);
            })
            ->latest() // Order the results by the most recent first.
            ->limit(10) // Limit the results to the 10 most recent activities.
            ->get(); // Execute the query and get the results

        return $activities;
    }

    /**
     * Filter activities to get only those related to vacancies
     * @return Collection
     */
    public function getActivityRelatedVacancies(): Collection
    {
        $vacancyActivities = Activity::where('subject_type', 'App\Models\Vacancy')->get();
        return $vacancyActivities;
    }


    public function getActivityRelatedVacancyIds()
    {
        $vacancyActivities = Activity::where('subject_type', 'App\Models\Vacancy');
        $vacancyIds = $vacancyActivities->pluck('subject_id');
        return $vacancyIds;
    }

    /**
     * For activities where messages have been deleted, extract the 'to_id' from the old properties.
     * @return mixed
     */
    public function deleteActivityMessage(): mixed
    {
        $deletedMessageUserIds = Activity::where('subject_type', 'App\Models\Message')
            ->where('event', 'deleted')
            ->get()
            ->map(function ($activity) {
                return data_get($activity, 'properties.old.to_id');
            })
            ->filter()
            ->unique();

        return $deletedMessageUserIds;
    }

    /**
     * Define the models that are relevant for the activity log.
     * @return array
     */
    private function allowedModels(): array
    {
        return [
            'App\Models\Applicant',
            'App\Models\Application',
            'App\Models\Vacancy',
            'App\Models\Message',
            'App\Models\User'
        ];
    }
}
