<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EmailTemplate extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'email_templates';

    protected $fillable = [
        'role_id',
        'name',
        'subject',
        'greeting',
        'intro',
        'outro',
        'icon',
        'color',
    ];

    //Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    //Notifications
    public function emails()
    {
        return $this->hasMany(Email::class, 'type_id');
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
