<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Reminder extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'reminder_setting_id',
        'email_template_id'
    ];

    //User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //Reminder Setting
    public function reminderSetting()
    {
        return $this->belongsTo(ReminderSetting::class, 'reminder_setting_id');
    }

    //Email Template
    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
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
