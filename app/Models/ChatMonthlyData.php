<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ChatMonthlyData extends Model
{
    use HasFactory;
    //use LogsActivity;

    protected $table = 'chat_monthly_data';

    protected $fillable = [
        'chat_total_data_id',
        'chat_type',
        'month',
        'count',
    ];

    // Chat Total Data
    public function totalData()
    {
        return $this->belongsTo(ChatTotalData::class, 'chat_total_data_id');
    }

    /**
     * The attributes that should be logged.
     * @var bool
     */
    // protected static $logAttributes = ['*']; // Commented out to disable logging

    //Activity Log
    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logFillable();
    // }
}
