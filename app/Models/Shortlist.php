<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Shortlist extends Model
{
    use HasFactory, LogsActivity;
    
    protected $fillable = [
        'user_id',
        'vacancy_id',
        'applicant_ids'
    ];

    //User
    public function user()
    {
        return $this->belongsTo(User::class);
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
