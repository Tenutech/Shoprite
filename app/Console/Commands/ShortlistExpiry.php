<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Applicant;
use App\Models\Shortlist;
use App\Models\Interview;
use App\Models\Setting;
use Carbon\Carbon;

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
        // Fetch the shortlist_expiry setting from the database
        $shortlistExpirySetting = Setting::where('key', 'shortlist_expiry')->first();
        $shortlistExpiryDays = $shortlistExpirySetting ? (int)$shortlistExpirySetting->value : 14; // Default to 14 days if not set

        $shortlists = Shortlist::all();

        foreach ($shortlists as $shortlist) {
            // Calculate the expiry date from the shortlist's creation date
            $expiryDate = Carbon::parse($shortlist->created_at)->addDays($shortlistExpiryDays);

            // Check if the current date is greater than or equal to the expiry date
            if (Carbon::now()->gte($expiryDate)) {
                //Applicant Ids
                $applicantIds = json_decode($shortlist->applicant_ids, true);

                foreach ($applicantIds as $key => $applicantId) {
                    // Check if an interview exists for the applicant within the expiry period
                    $interviews = Interview::where('applicant_id', $applicantId)
                                          ->where('vacancy_id', $shortlist->vacancy_id)
                                          ->get();

                    // Default to removing the applicant unless a valid interview condition is met
                    $removeApplicant = true;

                    if ($interviews->isNotEmpty()) {
                        // Get the most recent interview
                        $lastInterview = $interviews->last();
                        $lastInterviewDatePlusExpiry = Carbon::parse($lastInterview->scheduled_date)->addDays($shortlistExpiryDays);

                        // Check if today is <= the last interview's scheduled_date plus shortlistExpiryDays and status is 'Scheduled'
                        if (Carbon::now()->lte($lastInterviewDatePlusExpiry) && in_array($lastInterview->status, ['Scheduled', 'Confirmed', 'Reschedule', 'Completed', 'Appointed'])) {
                            $removeApplicant = false;
                        }
                    }

                    if ($removeApplicant) {
                        // Remove applicant from the array
                        unset($applicantIds[$key]);

                        // Additionally, fetch the Applicant model and update the 'shortlist_id' attribute to null if 'appointed_id' is null
                        $applicant = Applicant::find($applicantId);
                        if ($applicant && is_null($applicant->appointed_id) && $applicant->shortlist_id == $shortlist->id) {
                            $applicant->shortlist_id = null;
                            $applicant->save();
                        }
                    }
                }

                // Update the shortlist with the modified applicant IDs array
                $shortlist->update(['applicant_ids' => json_encode(array_values($applicantIds))]);
            }
        }

        $this->info('Shortlist applicants updated successfully.');
    }
}
