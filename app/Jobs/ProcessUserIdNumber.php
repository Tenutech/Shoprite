<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessUserIdNumber implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;

    /**
     * Create a new job instance.
     *
     * @param int $userId The ID of the user to process.
     * @return void
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $user = User::find($this->userId);
        
        if ($user) {
            // Extract birth date
            $year = substr($user->id_number, 0, 2);
            $month = substr($user->id_number, 2, 2);
            $day = substr($user->id_number, 4, 2);
            // Correct for century ambiguity
            $century = $year >= date('y') ? '19' : '20';
            $birthdate = $century . $year . '-' . $month . '-' . $day;

            // Calculate age
            $age = \Carbon\Carbon::parse($birthdate)->age;

            // Determine gender (SSSS)
            $genderCode = substr($user->id_number, 6, 4);
            $genderId = $genderCode < 5000 ? 2 : 1; // Female: 2, Male: 1

            // Citizenship status (C)
            $citizen = substr($user->id_number, 10, 1);

            if ($this->isValidSAIdNumber($user->id_number)) {
                $verified = 'Yes';
            } else {
                $verified = 'No';
            }

            // Update user
            $user->birth_date = $birthdate;
            $user->age = $age;
            $user->gender_id = $genderId;
            $user->citizen = $citizen;
            $user->id_verified = $verified;
            $user->save();
        }
    }

    /**
     * Check if the given ID number is a valid South African ID number.
     */
    protected function isValidSAIdNumber($id): bool
    {
        $id = preg_replace('/\D/', '', $id); // Ensure the ID is only digits
        if (strlen($id) != 13) {
            return false; // Early return if ID length is incorrect
        }

        $sum = 0;
        $length = strlen($id);
        for ($i = 0; $i < $length - 1; $i++) { // Exclude the last digit for the main loop
            $number = (int)$id[$i];
            if (($length - $i) % 2 === 0) {
                $number = $number * 2;
                if ($number > 9) {
                    $number = $number - 9;
                }
            }
            $sum += $number;
        }

        // Calculate checksum based on the sum
        $checksum = (10 - ($sum % 10)) % 10;

        // Last digit of the ID should match the calculated checksum
        return (int)$id[$length - 1] === $checksum;
    }
}
