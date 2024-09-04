<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidSAIdNumber implements Rule
{
    public function passes($attribute, $value)
    {
        $id = preg_replace('/\D/', '', $value);
        
        if (strlen($id) != 13) {
            return false;
        }

        $sum = 0;
        $length = strlen($id);
        for ($i = 0; $i < $length - 1; $i++) {
            $number = (int) $id[$i];
            if (($length - $i) % 2 === 0) {
                $number = $number * 2;
                if ($number > 9) {
                    $number = $number - 9;
                }
            }
            $sum += $number;
        }

        $checksum = (10 - ($sum % 10)) % 10;

        return (int) $id[$length - 1] === $checksum;
    }

    public function message()
    {
        return 'The ID number you entered is invalid.';
    }
}