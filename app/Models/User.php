<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmail;
use App\Notifications\ResetPasswordEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Log;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use LogsActivity;
    use Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'email_verified_at',
        'phone',
        'id_number',
        'address',
        'id_verified',
        'password',
        'avatar',
        'birth_date',
        'age',
        'gender_id',
        'resident',
        'company_id',
        'position_id',
        'role_id',
        'applicant_id',
        'store_id',
        'division_id',
        'region_id',
        'brand_id',
        'internal',
        'status_id'
    ];

    protected $appends = ['updated_at_human'];

    public function getUpdatedAtHumanAttribute()
    {
        return $this->updated_at->diffForHumans();
    }

    //User Gender
    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id');
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

    //User Store
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    //User Division
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    //User Region
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    //User Brand
    public function brand()
    {
        return $this->belongsTo(Brand::class);
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
        return $this->belongsToMany(Applicant::class, 'applicant_save', 'user_id', 'applicant_id')->withTimestamps();
        ;
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

    public function getApplicationStatusAndColor($approved)
    {
        switch ($approved) {
            case 'Yes':
                return ['name' => 'Approved', 'color' => 'success'];
            case 'Pending':
                return ['name' => 'Pending', 'color' => 'warning'];
            case 'No':
                return ['name' => 'Declined', 'color' => 'danger'];
            default:
                return ['name' => 'Unknown', 'color' => 'secondary'];
        }
    }

    //Notification Settings
    public function notificationSettings()
    {
        return $this->hasOne(NotificationSetting::class);
    }

    /**
     * Determine if the user can receive a specific type of notification.
     *
     * @param string $notificationType The type of notification to check.
     * @return bool
     */
    public function canReceiveNotificationType(string $notificationType): bool
    {
        $settings = $this->notificationSettings;

        if (!$settings) {
            return false; // Assume opt-out if settings are not found
        }

        // Example for checking a specific notification type
        switch ($notificationType) {
            case 'Submitted application 🔔':
                return $settings->notify_application_submitted ?? false;
            case 'Approved your application request ✅':
                return $settings->notify_application_status ?? false;
            case 'Declined your application request 🚫':
                return $settings->notify_application_status ?? false;
            case 'You have been Shortlisted ✨':
                return $settings->notify_shortlisted ?? false;
            case 'Interview Scheduled 📅':
                return $settings->notify_interview ?? false;
            case 'Confirmed your interview request ✅':
                return $settings->notify_interview ?? false;
            case 'Declined your interview request 🚫':
                return $settings->notify_interview ?? false;
            case 'Requested to reschedule 📅':
                return $settings->notify_interview ?? false;
            case 'Completed your interview 🚀':
                return $settings->notify_interview ?? false;
            case 'You have been Appointed 🎉':
                return $settings->receive_email_notifications ?? false;
            case 'Has been declined 🚫':
                return $settings->receive_email_notifications ?? false;
            case 'Created new vacancy 🔔':
                return $settings->notify_vacancy_status ?? false;
            case 'Has been approved 🎉':
                return $settings->notify_vacancy_status ?? false;
            case 'Needs amendment 📝':
                return $settings->notify_vacancy_status ?? false;
            case 'Has been declined ❌':
                return $settings->notify_vacancy_status ?? false;
            case 'Has applied for vacancy 🔔':
                return $settings->notify_application_submitted ?? false;
            default:
                return false;
        }
    }

    /**
     * Send a notification to the user, if they have opted in for that type.
     *
     * @param Notification $notification The notification instance to send.
     * @param string $notificationType The type of notification being sent.
     * @return void
     */
    public function sendCustomNotification($notification, string $notificationType)
    {
        if ($this->canReceiveNotificationType($notificationType)) {
            $this->notify($notification);
        } else {
            // Optionally log or handle cases where the user has opted out
            //Log::info("User ({$this->id}) has opted out of {$notificationType} notifications.");
        }
    }

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
            $this->notify(new VerifyEmail());

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

    public function sendPasswordResetNotification($token)
    {
        try {
            $this->notify(new ResetPasswordEmail($token));

            Email::create([
                'user_id'      => $this->id,
                'subject_type' => get_class($this),
                'subject_id'   => $this->id,
                'template_id'  => 23
            ]);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
        }
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
