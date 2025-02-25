<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Statistic extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'role_id',
        'store_id',
        'region_id',
        'division_id',
        'value',
    ];

    protected $casts = [
        'value' => 'decimal:2', // Ensures value has 2 decimal places
    ];

    // Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Region
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    // Division
    public function division()
    {
        return $this->belongsTo(Division::class);
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
