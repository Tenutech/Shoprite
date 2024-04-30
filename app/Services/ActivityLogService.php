<?php

namespace App\Services;

use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    public function getActivityLog($allowedModels, $authUserId, $authVacancyIds)
    {
        return Activity::whereIn('subject_type', $allowedModels)
            ->where(function ($query) use ($authUserId, $authVacancyIds) {
                $query->where('causer_id', $authUserId)
                    ->whereIn('event', ['created', 'updated', 'deleted']);
            })
            ->orWhere(function ($q) use ($authUserId) {
                $q->where('event', 'accessed')
                    ->whereIn('description', ['job-overview.index', 'applicant-profile.index'])
                    ->where('causer_id', $authUserId);
            })
            ->orWhere(function ($q) use ($authUserId) {
                $q->where('subject_type', 'App\Models\Message')
                    ->where('properties->attributes->to_id', $authUserId)
                    ->where('event', 'created');
            })
            ->orWhere(function ($q) use ($authVacancyIds) {
                $q->where('subject_type', 'App\Models\Application')
                    ->whereIn('properties->attributes->vacancy_id', $authVacancyIds);
            })
            ->latest()
            ->limit(10)
            ->get();
    }
}
