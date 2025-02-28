<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Applicant;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TalentpoolRRP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'talentpool:RRP';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Moves RRP appointed applicants back to the talent pool after a set period.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fetch the auto-placement setting for RRP employment
        $setting = Setting::where('key', 'auto_placed_back_in_talent_pool_rrp')->first();
        $weeksLimit = $setting ? (int) $setting->value : 7; // Default to 7 weeks if not set

        // Calculate the cutoff date
        $expiryDate = Carbon::now()->subWeeks($weeksLimit);

        // Fetch applicants with employment = 'R' who were appointed and their vacancy_fill->created_at is older than the cutoff
        $applicants = Applicant::whereNotNull('appointed_id')
            ->where('employment', 'R')
            ->whereHas('vacancyFill', function ($query) use ($expiryDate) {
                $query->where('created_at', '<=', $expiryDate);
            })
            ->get();

        $count = 0;

        foreach ($applicants as $applicant) {
            // Remove the appointment details
            $applicant->appointed_id = null;
            $applicant->shortlist_id = null;
            $applicant->save();

            $count++;
        }

        $this->info("{$count} RRP appointed applicants have been moved back to the talent pool.");
    }
}