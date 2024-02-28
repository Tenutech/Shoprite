<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ApplicantMonthlyStoreData extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'applicant_monthly_store_data';
    
    protected $fillable = [
        'store_id',
        'applicant_monthly_data_id',
        'count',
    ];

    // Store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Monthly Data
    public function monthlyData()
    {
        return $this->belongsTo(ApplicantMonthlyData::class, 'applicant_monthly_data_id');
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
