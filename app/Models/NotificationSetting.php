<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NotificationSetting extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'notification_settings';

    protected $fillable = [
        'user_id',
        'receive_email_notifications',
        'receive_whatsapp_notifications',
        'notify_application_submitted',
        'notify_application_status',
        'notify_shortlisted',
        'notify_interview',
        'notify_vacancy_status',
        'notify_new_application',
    ];

    //User
    public function user()
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
