<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Applicant;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TalentpoolFixedTerm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'talentpool:fixed_term';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Moves fixed-term appointed applicants back to the talent pool after a set period.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scriptPath = base_path('python/commands/talentpool_fixed_term.py');

        $result = Process::run("python3 {$scriptPath}");
    
        if ($result->successful()) {
            $this->info(trim($result->output()));
        } else {
            $this->error('Python script failed.');
            Log::error('Talentpool script error', [
                'error' => $result->errorOutput(),
            ]);
        }
    }
}