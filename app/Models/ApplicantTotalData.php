<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ApplicantTotalData extends Model
{
    use HasFactory;

    //use LogsActivity;

    protected $table = 'applicant_total_data';

    protected $fillable = [
        'year',
        'total_applicants',
        'total_interviewd',
        'total_appointed',
        'total_time_to_appointed',
        'jan',
        'feb',
        'mar',
        'apr',
        'may',
        'jun',
        'jul',
        'aug',
        'sep',
        'oct',
        'nov',
        'dec',
    ];

    // Applicant Monthly Data
    public function monthlyData()
    {
        return $this->hasMany(ApplicantMonthlyData::class, 'applicant_total_data_id');
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
