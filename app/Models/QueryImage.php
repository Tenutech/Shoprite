<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class QueryImage  extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'query_id',
        'url'
    ];

    // Query
    public function helpQuery()
    {
        return $this->belongsTo(Query::class);
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
