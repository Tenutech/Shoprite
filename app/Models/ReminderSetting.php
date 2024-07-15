<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ReminderSetting extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'reminder_settings';
    
    protected $fillable = [
        'type',
        'role_id',
        'delay',
        'email_template_id',
        'is_active',
    ];

    //Role
    public function role()
    {
        return $this->belongsTo(Role::class);
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
