<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Province extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
    ];

    //Towns
    public function towns()
    {
        return $this->hasMany(Town::class);
    }

    // Applicants
    public function applicants()
    {
        return $this->hasManyThrough(Applicant::class, Town::class, 'province_id', 'town_id', 'id', 'id');
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
