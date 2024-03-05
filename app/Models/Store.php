<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Store extends Model
{
    use HasFactory, LogsActivity;
    
    protected $fillable = [
        'brand_id',
        'town_id'
    ];

    //Brand
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    //Town
    public function town()
    {
        return $this->belongsTo(Town::class);
    }

    //Vacancy
    public function vacancies()
    {
        return $this->hasMany(Vacancy::class);
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
