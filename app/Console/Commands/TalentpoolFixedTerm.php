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
        // Fetch the auto-placement setting for fixed-term employment
        $setting = Setting::where('key', 'auto_placed_back_in_talent_pool_fixed_term')->first();
        $daysLimit = $setting ? (int) $setting->value : 14; // Default to 14 days if not set

        // Calculate the cutoff date
        $expiryDate = Carbon::now()->subDays($daysLimit);

        // Fetch applicants with employment = 'F' who were appointed and their vacancy_fill->created_at is older than the cutoff
        $applicants = Applicant::whereNotNull('appointed_id')
            ->where('employment', 'F')
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

        $this->info("{$count} fixed-term appointed applicants have been moved back to the talent pool.");
    }
}
