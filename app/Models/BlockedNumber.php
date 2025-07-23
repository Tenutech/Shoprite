<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',  // The blocked number (e.g., +27871234567)
        'reason',        // Optional reason for blocking
    ];
}
