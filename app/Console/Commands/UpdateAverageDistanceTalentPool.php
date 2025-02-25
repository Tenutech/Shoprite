<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Statistic;
use App\Models\Setting;
use Carbon\Carbon;
use App\Services\DataService\ApplicantProximityService;

class UpdateAverageDistanceTalentPool extends Command
{
    /**
     * The name and signature of the console command.
     * This allows running the command manually using:
     * `php artisan update:average-distance-talent-pool`
     *
     * @var string
     */
    protected $signature = 'update:average-distance-talent-pool';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates and updates the average distance of talent pool applicants daily at midnight.';

    /**
     * The ApplicantProximityService instance.
     *
     * @var ApplicantProximityService
     */
    protected ApplicantProximityService $applicantProximityService;

    /**
     * Create a new command instance.
     *
     * @param ApplicantProximityService $applicantProximityService
     */
    public function __construct(ApplicantProximityService $applicantProximityService)
    {
        parent::__construct();
        $this->applicantProximityService = $applicantProximityService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting calculation and update for average distance talent pool applicants...');

        // Get the type
        $type = 'all';

        // Get the id
        $id = null;

        // Get the start date (first day of the same month, one year ago)
        $startDate = now()->subYear()->startOfMonth();

        // Get the end date (today)
        $endDate = now();

        // Retrieve the maximum allowed distance from settings or default to 50km
        $maxDistanceFromStore = Setting::where('key', 'max_distance_from_store')->value('value') ?? 50;

        // Calculate the average distance of talent pool applicants
        $averageDistance = $this->applicantProximityService->getAverageDistanceTalentPoolApplicantsDB($type, $id, $startDate, $endDate, $maxDistanceFromStore);

        // Validate the result
        if ($averageDistance === null) {
            $this->error('Failed to calculate the average distance. Exiting command.');
            return;
        }

        // Update the statistics table where name = 'average_distance_talent_pool' and role_id is either 1 or 2
        $affectedRows = Statistic::where('name', 'average_distance_talent_pool')
            ->whereIn('role_id', [1, 2])
            ->update([
                'value' => $averageDistance,
                'updated_at' => now(),
            ]);

        // Log the outcome
        if ($affectedRows > 0) {
            $this->info("Statistics updated successfully! New average distance: $averageDistance km");
        } else {
            $this->warn('No records found to update in the statistics table.');
        }
    }
}