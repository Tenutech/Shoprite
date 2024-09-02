<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Vacancy extends Model
{
    use HasFactory, LogsActivity;
    
    protected $fillable = [
        'user_id',
        'position_id',
        'store_id',
        'type_id',
        'status_id',
        'open_positions',
        'filled_positions',
        'advertisement'
    ];

    //User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //Position
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    //Store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    //Type
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    //Status
    public function status()
    {
        return $this->belongsTo(VacancyStatus::class, 'status_id');
    }

    //Applicants
    public function applicants()
    {
        return $this->belongsToMany(User::class, 'applications', 'vacancy_id', 'user_id')->withTimestamps()->withPivot('approved');
    }

    //Applications
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    //Saved By
    public function savedBy()
    {
        return $this->belongsToMany(User::class, 'vacancy_save', 'vacancy_id', 'user_id')->withTimestamps();
    }

    //Shortlists
    public function shortlists()
    {
        return $this->hasMany(Shortlist::class);
    }

    //Appointed
    public function appointed()
    {
        return $this->belongsToMany(Applicant::class, 'vacancy_fills', 'vacancy_id', 'applicant_id')->withTimestamps();
    }

    //Amendments
    public function amendments()
    {
        return $this->hasMany(Amendment::class);
    }

    //SAP Number
    public function sapNumbers()
    {
        return $this->hasMany(SapNumber::class);
    }

    // Unused SAP Numbers
    public function availableSapNumbers()
    {
        return $this->hasMany(SapNumber::class)
                    ->whereDoesntHave('vacancyFills', function($query) {
                        $query->where('vacancy_fills.vacancy_id', $this->id);
                    });
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
