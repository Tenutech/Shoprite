<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Chat extends Model
{
    use HasFactory;
    //use LogsActivity;

    protected $fillable = [
        'applicant_id',
        'message',
        'type_id',
        'message_id',
        'status',
        'code',
        'reason',
        'template'
    ];

    //User
    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    //Chat Type
    public function type()
    {
        return $this->belongsTo(ChatType::class, 'type_id');
    }

    /**
     * The attributes that should be logged.
     * @var bool
     */
    // protected static $logAttributes = ['*']; // Commented out to disable logging

    //Activity Log
    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logFillable();
    // }
}
