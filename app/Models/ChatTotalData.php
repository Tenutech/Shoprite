<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ChatTotalData extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'chat_total_data';
    
    protected $fillable = [
        'year',
        'total_incoming',
        'total_outgoing',
        'jan_incoming',
        'jan_outgoing',
        'feb_incoming',
        'feb_outgoing',
        'mar_incoming',
        'mar_outgoing',
        'apr_incoming',
        'apr_outgoing',
        'may_incoming',
        'may_outgoing',
        'jun_incoming',
        'jun_outgoing',
        'jul_incoming',
        'jul_outgoing',
        'aug_incoming',
        'aug_outgoing',
        'sep_incoming',
        'sep_outgoing',
        'oct_incoming',
        'oct_outgoing',
        'nov_incoming',
        'nov_outgoing',
        'dec_incoming',
        'dec_outgoing',
    ];

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
