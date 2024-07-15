<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ChatType extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'chat_types';
    
    protected $fillable = [
        'name',
        'icon',
        'color'
    ];

    //Chats
    public function chats()
    {
        return $this->hasMany(Chats::class, 'type_id');
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
