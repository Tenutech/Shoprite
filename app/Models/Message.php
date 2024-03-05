<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Message extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'from_id',
        'to_id',
        'message',
        'read'
    ];

    //User From
    public function from()
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    //User To
    public function to()
    {
        return $this->belongsTo(User::class, 'to_id');
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
