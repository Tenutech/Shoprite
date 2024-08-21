<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Language extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'icon',
        'color'
    ];

    //Applicants Read
    public function applicantsRead()
    {
        return $this->belongsToMany(Applicant::class, 'applicant_read_languages', 'language_id', 'applicant_id');
    }

    //Applicants Speak
    public function applicantsSpeak()
    {
        return $this->belongsToMany(Applicant::class, 'applicant_speak_languages', 'language_id', 'applicant_id');
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
