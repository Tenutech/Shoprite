<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Applicant;
use Carbon\Carbon;

class SyncQaUsers extends Command
{
    // Artisan command signature and description
    protected $signature = 'sync:qa-users';
    protected $description = 'Sync users from shoprite_qa to shoprite DB';

    // Arrays to track what was inserted and updated for logging
    protected $insertedUserIds = [];
    protected $updatedUserIds = [];

    public function handle()
    {
        // Step 1: Get users from shoprite_qa where role_id = 7 only
        $qaUsers = DB::connection('shoprite_qa')->table('users')
                    ->where('id', '>=', 100)
                    ->where('role_id', 7)
                    ->get();

        // Step 2: Loop through each QA user
        foreach ($qaUsers as $qaUser) {
            // Attempt to match a user by email, id_number, or phone
            $match = User::where('email', $qaUser->email)
                        ->orWhere('id_number', $qaUser->id_number)
                        ->orWhere('phone', $qaUser->phone)
                        ->first();

            // If we find a matching user, attempt to update if newer
            if ($match) {
                if (Carbon::parse($qaUser->updated_at)->gt($match->updated_at)) {
                    // Check that email/phone/id_number are not used by other users
                    $conflict = User::where(function ($query) use ($qaUser, $match) {
                        $query->where(function ($q) use ($qaUser) {
                            $q->where('email', $qaUser->email)
                              ->orWhere('phone', $qaUser->phone)
                              ->orWhere('id_number', $qaUser->id_number);
                        })->where('id', '!=', $match->id);
                    })->exists();

                    if (!$conflict) {
                        // Safe to update
                        $this->updateUser($match, (array) $qaUser);
                        $this->updatedUserIds[] = $match->id;
                        $this->info("✅ Updated User ID: {$match->id}");
                    } else {
                        $this->warn("⚠️ Skipped update due to conflict: {$qaUser->email} / {$qaUser->phone}");
                    }
                }
            } else {
                // Check if any user exists with same email/phone/id_number
                $conflict = User::where('email', $qaUser->email)
                            ->orWhere('id_number', $qaUser->id_number)
                            ->orWhere('phone', $qaUser->phone)
                            ->exists();

                if (!$conflict) {
                    // Safe to insert
                    $newUser = $this->insertUser((array) $qaUser);
                    $this->insertedUserIds[] = $newUser->id;
                    $this->info("🆕 Inserted New User ID: {$newUser->id}");
                } else {
                    $this->warn("⚠️ Skipped insert due to conflict: {$qaUser->email} / {$qaUser->phone}");
                }
            }
        }

        // Step 5: Log the sync result
        // Log::info('📝 QA Sync Summary — Inserted Users:', $this->insertedUserIds);
        // Log::info('📝 QA Sync Summary — Updated Users:', $this->updatedUserIds);
        $this->info('✅ Sync completed. Inserted and updated user IDs logged.');
    }

    /**
     * Update an existing user with new values
     */
    protected function updateUser($user, $data)
    {
        // Remove fields we should not update
        unset($data['id'], $data['applicant_id'], $data['created_at'], $data['updated_at']);

        // Update user and link applicant
        $user->fill($data)->save();
        $this->linkApplicant($user);
    }

    /**
     * Insert a new user from the QA dataset
     */
    protected function insertUser($data)
    {
        // Remove fields that Laravel manages or shouldn't be set
        unset($data['id'], $data['applicant_id'], $data['created_at'], $data['updated_at']);

        // Create the user and link the applicant
        $user = User::create($data);
        $this->linkApplicant($user);

        return $user;
    }

    /**
     * Link a user to an applicant if matching id_number is found
     */
    protected function linkApplicant($user)
    {
        if ($user->id_number) {
            $applicant = Applicant::where('id_number', $user->id_number)->first();
            if ($applicant) {
                $user->applicant_id = $applicant->id;
                $user->save();
                $this->info("🔗 Linked applicant ID: {$applicant->id} to user ID: {$user->id}");
            }
        }
    }
}