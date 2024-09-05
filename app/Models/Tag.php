<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Tag extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'icon',
        'color'
    ];

    //Positions
    public function positions()
    {
        return $this->belongsToMany(Position::class, 'position_tag', 'tag_id', 'position_id')->withTimestamps();
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
