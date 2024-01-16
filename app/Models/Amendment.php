<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Amendment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'opportunity_id',
        'causer_id',
        'description'
    ];

    //User

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //Opportunity

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    //Causer

    public function causer()
    {
        return $this->belongsTo(User::class);
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
