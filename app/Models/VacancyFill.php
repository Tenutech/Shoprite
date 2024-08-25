<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VacancyFill extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'vacancy_id',
        'applicant_id',
        'approved',
        'sap_number',
    ];

    //Applicant
    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    //Vacancy
    public function vacancy()
    {
        return $this->belongsTo(Vacancy::class);
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
