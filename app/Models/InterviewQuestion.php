<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InterviewQuestion extends Model
{
    use HasFactory, LogsActivity;
    
    protected $fillable = [
        'template_id',
        'question',
        'type',
        'sort',
    ];

    // Interview Template
    public function template()
    {
        return $this->belongsTo(InterviewTemplate::class);
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
