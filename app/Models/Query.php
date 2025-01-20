<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Query extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'jira_issue_id',
        'user_id',
        'firstname',
        'lastname',
        'email',
        'phone',
        'subject',
        'body',
        'category_id',
        'severity',
        'status',
        'answer'
    ];

    // User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Category
    public function category()
    {
        return $this->belongsTo(QueryCategory::class, 'category_id');
    }

    // Image
    public function images()
    {
        return $this->hasMany(QueryImage::class, 'query_id');
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
