<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ChatTemplate extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'chat_templates';

    protected $fillable = [
        'message',
        'state_id',
        'category_id',
        'answer',
        'sort'
    ];

    //Chat State

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    //Chat Type

    public function category()
    {
        return $this->belongsTo(ChatCategory::class, 'category_id');
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
