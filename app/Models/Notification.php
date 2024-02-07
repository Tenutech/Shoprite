<?php

namespace App\Models;

use App\Notifications\NotifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Notification extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'causer_id',
        'subject_type',
        'subject_id',
        'type_id',
        'notification',
        'read',
        'show'
    ];

    //User

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //Causer

    public function causer()
    {
        return $this->belongsTo(User::class);
    }

    //Notification Type

    public function type()
    {
        return $this->belongsTo(NotificationType::class, 'type_id');
    }

    //Subject

    public function subject()
    {
        return $this->morphTo();
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

    //Emial Notification

    protected static function booted()
    {
        static::created(function ($notification) {
            $user = $notification->user;

            // Check if the user has an email address
            if (!is_null($user->email)) {
                $user->notify((new NotifyEmail($notification))->onQueue('default'));
            }
        });
    }
}