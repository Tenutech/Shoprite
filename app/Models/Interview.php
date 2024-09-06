<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Interview extends Model
{
    use HasFactory;
    use LogsActivity;

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
        'score',
        'reschedule_date'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     * This is where we hook into model events.
     *
     * @return void
     */
    protected static function booted()
    {
        static::updated(function ($interview) {
            if ($interview->isDirty('status') && $interview->status === 'no-show') {
                if ($interview->applicant) {
                    $interview->applicant->increment('no_show');
                }
            }
        });
    }

    //Applicants
    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    //Interviewer
    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    //Vacancies
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
