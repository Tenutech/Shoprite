<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Statistic;
use App\Models\Setting;
use Carbon\Carbon;
use App\Services\DataService\ApplicantProximityService;
use Illuminate\Support\Facades\Process;

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

        $scriptPath = base_path('python/commands/talentpool_average_distance.py');

        $result = Process::run("python {$scriptPath}");

        if ($result->failed()) {
            $this->error('Python script failed: ' . $result->errorOutput());
            return;
        }

        // Log the outcome
        $this->info(trim($result->output()));
    }
}