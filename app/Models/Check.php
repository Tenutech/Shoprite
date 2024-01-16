<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Check extends Model
{
    use HasFactory, LogsActivity;
    
    protected $fillable = [
        'name',
        'icon',
        'color'
    ];

    //Applicants
    public function applicants() {
        return $this->belongsToMany(Applicant::class, 'applicant_checks')->withTimestamps()->withPivot('result', 'reason', 'file');
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
