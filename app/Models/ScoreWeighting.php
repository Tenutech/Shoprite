<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ScoreWeighting extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'score_type',
        'weight',
        'max_value',
        'condition_field',
        'condition_value',
        'fallback_value',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'max_value' => 'decimal:2',
        'fallback_value' => 'decimal:2',
    ];

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
