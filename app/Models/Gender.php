<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Gender extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'icon',
        'color'
    ];

    //Applicants
    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }

    //Users
    public function users()
    {
        return $this->hasMany(User::class);
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
