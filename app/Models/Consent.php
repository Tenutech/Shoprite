<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guardian_mobile_number',
        'consent_status',
        'consent_date',
    ];

    /**
     * Get the user associated with the consent.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
