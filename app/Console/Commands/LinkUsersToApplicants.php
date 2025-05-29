<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Applicant;

class LinkUsersToApplicants extends Command
{
    // Command name to use in Artisan
    protected $signature = 'users:link-applicants';

    // Description for `php artisan list`
    protected $description = 'Link users (role_id = 7, applicant_id is null) to matching applicants by id_number';

    public function handle()
    {
        // Step 1: Fetch all users with role_id = 7 and no applicant linked
        $users = User::where('role_id', 7)
                    ->whereNull('applicant_id')
                    ->get();

        // Counter to track how many users we update
        $linkedCount = 0;

        // Step 2: Loop through each user
        foreach ($users as $user) {
            // Step 3: Find a matching applicant by id_number
            if ($user->id_number) {
                $applicant = Applicant::where('id_number', $user->id_number)->first();

                // Step 4: If found, link the applicant_id to the user
                if ($applicant) {
                    $user->applicant_id = $applicant->id;
                    $user->save();

                    $linkedCount++;
                    $this->info("🔗 Linked user ID {$user->id} to applicant ID {$applicant->id}");
                }
            }
        }

        // Final report in terminal
        $this->info("✅ Completed. Total users linked to applicants: {$linkedCount}");
    }
}