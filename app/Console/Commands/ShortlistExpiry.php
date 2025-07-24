<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ShortlistExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shortlist:expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes applicant from the shortlist if no interview has been scheduled.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to remove applicants from shortlist if no interview has been scheduled...');

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

        $scriptPath = base_path('python/commands/shortlist_expiry.py');

        $process = new Process(['python', $scriptPath]);
        $process->setEnv($env);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->info(trim($process->getOutput()));
    }
}
