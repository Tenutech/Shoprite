<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'phone',
        'id_number',
        'password',
        'avatar',
        'company_id',
        'position_id',
        'website',
        'role_id',
        'applicant_id',
        'status_id'
    ];

    protected $appends = ['updated_at_human'];

    public function getUpdatedAtHumanAttribute() {
        return $this->updated_at->diffForHumans();
    }

    //User Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    //User Position
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    //User Role
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    //User Applicant
    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    //User Status
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    //User State
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    //Vacncies
    public function vacancies()
    {
        return $this->hasMany(Vacancy::class);
    }

    //Amendments
    public function amendments()
    {
        return $this->hasMany(Amendment::class);
    }

    //Applied Vacancies
    public function appliedVacancies()
    {
        return $this->belongsToMany(Vacancy::class, 'applications', 'user_id', 'vacancy_id')->withPivot('approved')->withTimestamps();
    }

    //Saved Vacancies
    public function savedVacancies()
    {
        return $this->belongsToMany(Vacancy::class, 'vacancy_save', 'user_id', 'vacancy_id')->withTimestamps();
    }

    //Saved Applicants
    public function savedApplicants()
    {
        return $this->belongsToMany(Applicant::class, 'applicant_save', 'user_id', 'applicant_id')->withTimestamps();;
    }

    //Files
    public function files()
    {
        return $this->hasMany(Document::class);
    }

    //Messages From
    public function messagesFrom()
    {
        return $this->hasMany(Message::class, 'from_id');
    }

    //Messages To
    public function messagesTo()
    {
        return $this->hasMany(Message::class, 'to_id');
    }

    //Notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    //Interviews
    public function interviews()
    {
        return $this->hasMany(Interview::class, 'interviewer_id');
    }

    //Shortlists
    public function shortlists()
    {
        return $this->hasMany(Shortlist::class);
    }

    /**
     * The attributes that should be logged.
     * @var bool
     */
    protected static $logAttributes = ['*'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        try {
            $this->notify(new VerifyEmail);
    
            Email::create([
                'user_id'      => $this->id,
                'subject_type' => get_class($this),
                'subject_id'   => $this->id,
                'template_id'  => 2
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
        }
    }
}
