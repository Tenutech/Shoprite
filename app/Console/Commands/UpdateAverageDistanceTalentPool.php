<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DataService\ApplicantProximityService;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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

        // Get credentials using config (not env)
        $dbConnection = config('database.default');
        $dbConfig = config("database.connections.$dbConnection");

        $env = [
            'DB_HOST' => $dbConfig['host'],
            'DB_PORT' => (string) $dbConfig['port'],
            'DB_DATABASE' => $dbConfig['database'],
            'DB_USERNAME' => $dbConfig['username'],
            'DB_PASSWORD' => $dbConfig['password'],
        ];

        $scriptPath = base_path('python/commands/talentpool_average_distance.py');

        $process = new Process(['python', $scriptPath]);
        $process->setEnv($env);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->info(trim($process->getOutput()));
    }
}