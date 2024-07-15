<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ChatCategory extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'chat_categories';

    protected $fillable = [
        'name'
    ];

    //Messages
    public function chatTemplates()
    {
        return $this->hasMany(ChatTemplate::class, 'category_id');
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
