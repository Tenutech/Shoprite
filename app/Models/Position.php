<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Position extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'image',
        'template_id'
    ];

    //User Position
    public function users()
    {
        return $this->hasMany(User::class);
    }

    //Applicants
    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }

    //Applicants Previous Job Position
    public function previousApplicants()
    {
        return $this->hasMany(Applicant::class, 'previous_job_position_id');
    }

    //Vacancy
    public function vacancies()
    {
        return $this->hasMany(Vacancy::class);
    }

    //Tags
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'position_tag', 'position_id', 'tag_id')->withTimestamps();
    }

    // Responsibilities
    public function responsibilities()
    {
        return $this->hasMany(Responsibility::class);
    }

    // Qualifications
    public function qualifications()
    {
        return $this->hasMany(Qualification::class);
    }

    // Skills
    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    // Experience Requirements
    public function experienceRequirements()
    {
        return $this->hasMany(ExperienceRequirement::class);
    }

    // Physical Requirements
    public function physicalRequirements()
    {
        return $this->hasMany(PhysicalRequirement::class);
    }

    // Working Hours
    public function workingHours()
    {
        return $this->hasMany(WorkingHour::class);
    }

    // Salary & Benefits
    public function salaryBenefits()
    {
        return $this->hasMany(SalaryBenefit::class);
    }

    // Success Factors
    public function successFactors()
    {
        return $this->hasMany(SuccessFactor::class);
    }

    //Files
    public function files()
    {
        return $this->hasMany(File::class);
    }

    // Interview Template
    public function interviewTemplate()
    {
        return $this->belongsTo(InterviewTemplate::class, 'template_id');
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
