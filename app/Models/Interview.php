<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Interview extends Model
{
    use HasFactory, LogsActivity;
    
    protected $fillable = [
        'applicant_id',
        'interviewer_id',
        'vacancy_id',
        'scheduled_date',
        'start_time',
        'end_time',
        'location',
        'notes',
        'status',
        'score'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    //Applicants
    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    //Interviewer
    public function interviewer()
    {
        return $this->belongsTo(User::class);
    }

    //Vacancies
    public function vacancy()
    {
        return $this->belongsTo(Vacancy::class);
    }

    //Interviews
    public function interviews()
    {
        return $this->hasMany(Interview::class);
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
