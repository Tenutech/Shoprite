<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NotificationType extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'notification_types';

    protected $fillable = [
        'name',
        'icon',
        'color',
    ];

    //Notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'type_id');
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
