<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Responsibility extends Model
{
    use HasFactory, LogsActivity;
    
    protected $fillable = [
        'position_id',
        'description',
        'icon',
        'color'
    ];

    //Position
    public function position()
    {
        return $this->belongsTo(Position::class);
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
