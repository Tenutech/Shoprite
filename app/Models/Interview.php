<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Crypt;

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
        'reschedule_date',
        'reschedule_by'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Add the encrypted_id field to be included in the response
    protected $appends = ['encrypted_id'];

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

    // Eencrypted Id
    public function getEncryptedIdAttribute()
    {
        return Crypt::encryptString($this->attributes['id']);
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
