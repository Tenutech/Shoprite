<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SapNumber extends Model
{
    use HasFactory, LogsActivity;
    
    protected $fillable = [
        'vacancy_id',
        'sap_number',
        'description',
    ];

    //Vacancy
    public function vacancy()
    {
        return $this->belongsTo(Vacancy::class);
    }

    //Vacancy Fills
    public function vacancyFills()
    {
        return $this->hasMany(VacancyFill::class);
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
