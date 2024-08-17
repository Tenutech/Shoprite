<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InteractiveOption extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'interactive_options';

    protected $fillable = [
        'chat_template_id',
        'title',
        'description',
        'value'
    ];

    //Chat Template
    public function chatTemplate()
    {
        return $this->belongsTo(ChatTemplate::class, 'chat_template_id');
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
