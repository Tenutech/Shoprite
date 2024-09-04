<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Town extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'province_id',
        'district',
        'seat',
        'class'
    ];

    //Province
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    //Applicants
    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }

    //Stores
    public function stores()
    {
        return $this->hasMany(Store::class);
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
