<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ApplicantMonthlyData extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'applicant_monthly_data';

    protected $fillable = [
        'applicant_total_data_id',
        'category_id',
        'category_type',
        'month',
        'count',
    ];

    // Applicant Total Data
    public function totalData()
    {
        return $this->belongsTo(ApplicantTotalData::class, 'applicant_total_data_id');
    }

    // Applicant Monthly Store Data
    public function monthlyStoreData()
    {
        return $this->hasMany(ApplicantMonthlyStoreData::class, 'applicant_monthly_data_id');
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
