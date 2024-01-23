<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Applicant extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'phone',
        'firstname',
        'lastname',        
        'id_number',  
        'location',        
        'town_id',
        'coordinates',
        'contact_number',
        'additional_contact_number',
        'gender_id',
        'race_id',
        'has_email',        
        'email',
        're_enter_email',
        'has_tax',
        'tax_number',
        'citizen',
        'foreign_national',
        'criminal',
        'avatar',
        'position_id',
        'position_specify',
        'school',
        'education_id',
        'training',
        'other_training',
        'drivers_license',
        'drivers_license_code',
        'job_previous',
        'reason_id',
        'job_leave_specify',
        'job_business',
        'job_position',
        'duration_id',
        'job_salary',
        'job_reference_name',
        'job_reference_phone',
        'retrenchment_id',
        'job_retrenched_specify',
        'brand_id',
        'previous_job_position_id',
        'job_shoprite_position_specify',
        'job_shoprite_leave',
        'transport_id',
        'transport_specify',
        'disability_id',
        'illness_specify',
        'commencement',
        'type_id',
        'application_reason_specify',
        'relocate',
        'relocate_town',
        'vacancy',
        'shift',
        'has_bank_account',
        'bank_id',
        'bank_specify',
        'bank_number',
        'expected_salary',
        'literacy_question_pool',
        'literacy_score',
        'literacy_questions',
        'literacy',
        'numeracy_question_pool',
        'numeracy_score',
        'numeracy_questions',
        'numeracy',
        'score',
        'role_id',
        'applicant_type_id',
        'state_id',
        'checkpoint',
        'created_at',
        'updated_at',
    ];

    //Applicant Town
    public function town()
    {
        return $this->belongsTo(Town::class);
    }

    //Applicant Gender
    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    //Applicant Race
    public function race()
    {
        return $this->belongsTo(Race::class);
    }

    //Applicant Position
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    //Applicant Race
    public function education()
    {
        return $this->belongsTo(Education::class);
    }

    //Read Languages
    public function readLanguages()
    {
        return $this->belongsToMany(Language::class, 'applicant_read_languages', 'applicant_id', 'language_id');
    }

    //Speak Languages
    public function speakLanguages()
    {
        return $this->belongsToMany(Language::class, 'applicant_speak_languages', 'applicant_id', 'language_id');
    }

    //Previous Job Leave Reason
    public function reason()
    {
        return $this->belongsTo(Reason::class);
    }

    //Previous Job Duration
    public function duration()
    {
        return $this->belongsTo(Duration::class);
    }

    //Retrenchment
    public function retrenchment()
    {
        return $this->belongsTo(Retrenchment::class);
    }

    //Brand
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    //Applicant Previous Job Position
    public function previousPosition()
    {
        return $this->belongsTo(Position::class, 'previous_job_position_id');
    }

    //Applicant Transport
    public function transport()
    {
        return $this->belongsTo(Transport::class);
    }

    //Disability
    public function disability()
    {
        return $this->belongsTo(Disability::class);
    }

    //Job Type
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    //Bank
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    //Applicant Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    //Applicant State
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    //Applicant Chats
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    //Saved By Users
    public function savedBy()
    {
        return $this->belongsToMany(User::class, 'applicant_save', 'applicant_id', 'user_id')->withTimestamps();
    }

    //User
    public function user()
    {
        return $this->hasOne(User::class);
    }

    //Applicant Type
    public function applicantType()
    {
        return $this->belongsTo(ApplicantType::class);
    }

    //Interviews
    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }

    //Checks
    public function checks() {
        return $this->belongsToMany(Check::class, 'applicant_checks')->withTimestamps()->withPivot('result', 'reason', 'file', 'updated_at');
    }

    //Latest Checks
    public function latestChecks() {
        return $this->belongsToMany(Check::class, 'applicant_checks')
                    ->withTimestamps()
                    ->withPivot('result', 'reason', 'file', 'updated_at')
                    ->whereRaw('applicant_checks.updated_at IN (select MAX(ac2.updated_at) from applicant_checks as ac2 where ac2.applicant_id = applicant_checks.applicant_id and ac2.check_id = applicant_checks.check_id group by ac2.check_id)');
    }

    //Interviews
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    //vacanciesFilled
    public function vacanciesFilled()
    {
        return $this->belongsToMany(Vacancy::class, 'vacancy_fills', 'applicant_id', 'vacancy_id')->withTimestamps();
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
