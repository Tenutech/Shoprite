<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Brand extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'icon',
        'color'
    ];

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

    //Applicants Brand
    public function applicantsBrand()
    {
        return $this->belongsToMany(Applicant::class, 'applicant_brand', 'brand_id', 'applicant_id');
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
