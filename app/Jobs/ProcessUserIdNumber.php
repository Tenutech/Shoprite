<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessUserIdNumber implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $userId;
    protected $applicantId;

    /**
     * Create a new job instance.
     *
     * @param int|null $userId The ID of the user to process.
     * @param int|null $applicantId The ID of the applicant to process.
     * @return void
     */
    public function __construct($userId = null, $applicantId = null)
    {
        $this->userId = $userId;
        $this->applicantId = $applicantId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // Determine which model to update based on provided ID
        if ($this->userId) {
            $person = User::find($this->userId);
        } elseif ($this->applicantId) {
            $person = Applicant::find($this->applicantId);
        } else {
            return;
        }

        if ($person) {
            // Extract birth date
            $year = substr($person->id_number, 0, 2);
            $month = substr($person->id_number, 2, 2);
            $day = substr($person->id_number, 4, 2);
            // Correct for century ambiguity
            $century = $year >= date('y') ? '19' : '20';
            $birthdate = $century . $year . '-' . $month . '-' . $day;

            // Calculate age
            $age = \Carbon\Carbon::parse($birthdate)->age;

            // Determine gender (SSSS)
            $genderCode = substr($person->id_number, 6, 4);
            $genderId = $genderCode < 5000 ? 2 : 1; // Female: 2, Male: 1

            // Citizenship status (C)
            if ($this->userId) {
                $resident = substr($person->id_number, 10, 1);
            }

            if (ProcessUserIdNumber::isValidSAIdNumber($person->id_number)) {
                $verified = 'Yes';
            } else {
                $verified = 'No';
            }

            // Update Person
            if (empty($person->birth_date)) {
                $person->birth_date = $birthdate;
            }

            if (is_null($person->age) || $person->age === '') {
                $person->age = $age;
            }

            if ($age < 18) {
                $person->under_18 = 'Yes';
            } else {
                $person->under_18 = 'No';
            }

            if (empty($person->gender_id)) {
                $person->gender_id = $genderId;
            }

            if (empty($person->resident) && $this->userId) {
                $person->resident = $resident;
            }

            if (empty($person->id_verified)) {
                $person->id_verified = $verified;
            }

            $person->save();
        }
    }

    /**
     * Check if the given ID number is a valid South African ID number.
     */
    public static function isValidSAIdNumber($id): bool
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
