<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VacancyStatus extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'vacancy_status';
    
    protected $fillable = [
        'name',
        'icon',
        'color'
    ];

    //Vacancies
    public function vacancies()
    {
        return $this->hasMany(Opportunity::class, 'status_id');
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
