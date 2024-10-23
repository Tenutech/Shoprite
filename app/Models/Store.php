<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Store extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'brand_id',
        'town_id',
        'address',
        'coordinates',
        'region_id',
        'division_id',
        'code'
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

    //Region
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    //Division
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    //Users
    public function users()
    {
        return $this->hasMany(User::class);
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
