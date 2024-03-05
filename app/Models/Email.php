<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Email extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'subject_type',
        'subject_id',
        'template_id'
    ];

    //User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //Email Template
    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    //Subject
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * The attributes that should be logged.
     * @var bool
     */
    protected static $logAttributes = ['*'];

    //Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable();
    }
}
