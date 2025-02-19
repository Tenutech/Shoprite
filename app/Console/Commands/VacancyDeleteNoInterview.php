<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vacancy;
use App\Models\Shortlist;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VacancyDeleteNoInterview extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vacancy:delete_no_interview';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marks vacancies as deleted if they exceed the posting duration and have no valid shortlists or interviews scheduled.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fetch the vacancy posting duration setting from the database
        $vacancyPostingDurationSetting = Setting::where('key', 'vacancy_posting_duration')->first();
        $vacancyPostingDays = $vacancyPostingDurationSetting ? (int)$vacancyPostingDurationSetting->value : 14; // Default to 14 days if not set

        // Fetch all vacancies older than the specified duration
        $expiryDate = Carbon::now()->subDays($vacancyPostingDays);

        $vacancies = Vacancy::where('created_at', '<=', $expiryDate)
                    ->where('deleted', 'No')
                    ->where('auto_deleted', 'No')
                    ->where(function ($query) {
                        $query->doesntHave('shortlists') // Vacancies with no shortlists
                            ->orWhereHas('shortlists', function ($subQuery) {
                                $subQuery->whereNull('applicant_ids')
                                        ->orWhereRaw("json_length(applicant_ids) = 0");
                            });
                    })
                    ->doesntHave('appointed') // Ensure no applicants have been appointed
                    ->doesntHave('interviews') // Ensure no interviews exist
                    ->get();

        foreach ($vacancies as $vacancy) {
            $vacancy->deleted = 'Yes';
            $vacancy->auto_deleted = 'Yes';
            $vacancy->save();
        }

        $this->info('Expired vacancies with no applicants or interviews have been marked as deleted.');
    }
}