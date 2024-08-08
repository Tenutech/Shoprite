<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InterviewTemplate extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [];

    // Interview Questions
    public function interviewQuestions()
    {
        return $this->hasMany(InterviewQuestion::class);
    }

    //Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }
}