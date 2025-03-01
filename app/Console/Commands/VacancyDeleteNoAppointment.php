<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Models\Vacancy;
use App\Models\Shortlist;
use App\Models\Applicant;
use App\Jobs\SendWhatsAppMessage;
use Carbon\Carbon;

class VacancyDeleteNoAppointment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vacancy:delete_no_appointment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marks vacancies as deleted if they exceed the posting duration and have no appointments, while sending regret messages to applicants with scheduled interviews.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fetch the vacancy posting duration setting for no appointment
        $vacancyPostingDurationSetting = Setting::where('key', 'vacancy_posting_duration_no_appointment')->first();
        $vacancyPostingDays = $vacancyPostingDurationSetting ? (int)$vacancyPostingDurationSetting->value : 30;  // Default to 30 days if not set

        // Fetch all vacancies older than the specified duration
        $expiryDate = Carbon::now()->subDays($vacancyPostingDays);

        $vacancies = Vacancy::where('created_at', '<=', $expiryDate)
                    ->where('deleted', 'No')
                    ->where('auto_deleted', 'No')
                    ->where('open_positions', '>', 0)
                    ->get();

        foreach ($vacancies as $vacancy) {
            $filledPositions = $vacancy->filled_positions;
            
            if ($filledPositions == 0) {
                $vacancy->deleted = 'Yes';
                $vacancy->auto_deleted = 'Yes';
            } else {
                $vacancy->open_positions = 0;
            }
            
            $vacancy->save();

            // Fetch applicants in shortlist with scheduled interviews
            $shortlists = Shortlist::where('vacancy_id', $vacancy->id)->get();

            foreach ($shortlists as $shortlist) {
                // Decode the current applicant_ids JSON into an array; default to empty array if null or invalid
                $applicantIds = json_decode($shortlist->applicant_ids, true) ?: [];
    
                // Get all applicants where shortlist_id is $shortlist->id
                $applicants = Applicant::where('shortlist_id', $shortlist->id)->get();

                foreach ($applicants as $applicant) {
                    // Check if the applicant has any interviews for the related vacancy
                    $hasInterviews = $applicant->interviews()->where('vacancy_id', $applicant->shortlist->vacancy_id)
                        ->whereIn('status', ['Scheduled', 'Confirmed', 'Reschedule', 'Completed'])
                        ->exists();

                    // If they have interviews, send a regret message
                    if ($hasInterviews) {
                        // Prepare regret message
                        $whatsappMessage = "Dear " . ($applicant->firstname ?: 'N/A') . ", thank you for your interest in the " .
                            (optional($vacancy->position)->name ?: 'N/A') . " position at " .
                            (optional($vacancy->store->brand)->name ?: 'N/A') . " (" .
                            (optional($vacancy->store->town)->name ?: 'N/A') . "). We truly appreciate the time and effort you invested in your application.
                            After careful consideration, we regret to inform you that we have selected another candidate for this role. Please know that this
                            decision does not diminish the value of your skills and experience.
                            We encourage you to apply for future opportunities with us, and we wish you all the best in your job search and career journey.";

                        // Define the message type and template
                        $type = 'template';
                        $template = 'regretted_2';
                        $variables = [
                            $applicant->firstname ?: 'N/A',
                            optional($vacancy->position)->name ?: 'N/A',
                            optional($vacancy->store->brand)->name ?: 'N/A',
                            optional($vacancy->store->town)->name ?: 'N/A'
                        ];

                        // Dispatch job to send WhatsApp message
                        SendWhatsAppMessage::dispatch($applicant, $whatsappMessage, $type, $template, $variables);
                    }

                    // Set shortlist_id to null for all applicants
                    $applicant->shortlist_id = null;
                    $applicant->save();

                    // Remove this applicant's ID from the applicantIds array if it exists
                    if (($key = array_search($applicant->id, $applicantIds)) !== false) {
                        unset($applicantIds[$key]);
                    }
                }
                
                // Re-index the array and encode it back to JSON, then save the shortlist
                $shortlist->applicant_ids = json_encode(array_values($applicantIds));
                $shortlist->save();
            }
        }

        $this->info('Expired vacancies with no appointments have been processed. Applicants with interviews have been notified.');
    }
}