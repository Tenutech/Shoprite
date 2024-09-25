<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Log;
use App\Jobs\UpdateApplicantData;

class Applicant extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'phone',
        'id_number',
        'id_verified',
        'under_18',
        'birth_date',
        'age',
        'gender_id',
        'firstname',
        'lastname',
        'race_id',
        'avatar_upload',
        'avatar',
        'terms_conditions',
        'additional_contact_number',
        'contact_number',
        'public_holidays',
        'education_id',
        'consent',
        'environment',
        'duration_id',
        'brand_id',
        'location_type',
        'location',
        'town_id',
        'coordinates',
        'has_email',
        'email',
        'disability',
        'literacy_question_pool',
        'literacy_score',
        'literacy_questions',
        'literacy',
        'numeracy_question_pool',
        'numeracy_score',
        'numeracy_questions',
        'numeracy',
        'situational_question_pool',
        'situational_score',
        'situational_questions',
        'situational',
        'score',
        'role_id',
        'applicant_type_id',
        'application_type',
        'shortlist_id',
        'appointed_id',
        'no_show',
        'user_delete',
        'state_id',
        'checkpoint',
    ];

    //Applicant Town
    public function town()
    {
        return $this->belongsTo(Town::class, 'town_id');
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

    //Applicant Race
    public function education()
    {
        return $this->belongsTo(Education::class);
    }

    //Previous Job Duration
    public function duration()
    {
        return $this->belongsTo(Duration::class);
    }

    //Brands
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'applicant_brands', 'applicant_id', 'brand_id');
    }

    //Applicant Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    //Applicant Shortlist
    public function shortlist()
    {
        return $this->belongsTo(Shortlist::class);
    }

    //Applicant Vacancy Fill
    public function vacancyFill()
    {
        return $this->belongsTo(VacancyFill::class, 'appointed_id');
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
    public function checks()
    {
        return $this->belongsToMany(Check::class, 'applicant_checks')->withTimestamps()->withPivot('result', 'reason', 'file', 'updated_at');
    }

    //Latest Checks
    public function latestChecks()
    {
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

    //Applicant Total Data
    protected static function booted()
    {
        //Create Applicant
        static::created(function ($applicant) {
            // Dispatch the job when an applicant is created
            UpdateApplicantData::dispatch($applicant->id, 'created');
        });

        //Update Appliant
        static::updated(function ($applicant) {
            // Dispatch the job when an applicant is updated
            UpdateApplicantData::dispatch($applicant->id, 'updated');
        });
    }

    //Applicant Monthly Data
    protected static function updateMonthlyData($yearlyDataId, $categoryType, $categoryId)
    {
        if ($categoryId) {
            $monthlyData = ApplicantMonthlyData::firstOrCreate([
                'applicant_total_data_id' => $yearlyDataId,
                'category_id' => $categoryId,
                'category_type' => $categoryType,
                'month' => now()->format('M')
            ], [
                'count' => 0 // default count
            ]);

            // Increment the count for the existing or newly created record
            $monthlyData->increment('count');
        }
    }

    //Handle Attribute Update
    protected static function handleAttributeUpdate($yearlyDataId, $attributeType, $applicant)
    {
        $attributeId = strtolower($attributeType) . "_id";
        if ($applicant->wasChanged($attributeId)) {
            $oldAttributeId = $applicant->getOriginal($attributeId);
            $newAttributeId = $applicant->$attributeId;

            if (!is_null($oldAttributeId)) {
                self::adjustMonthlyData($yearlyDataId, $attributeType, $oldAttributeId, false);
            }

            if (!is_null($newAttributeId)) {
                self::adjustMonthlyData($yearlyDataId, $attributeType, $newAttributeId, true);
            }
        }
    }

    //Handle Attribute Update For Province
    protected static function handleAttributeUpdateForProvince($yearlyDataId, $applicant)
    {
        // This assumes province_id is determined via the town relationship
        // Modify as necessary based on your actual data structure
        if ($applicant->wasChanged('town_id')) {
            $applicant->load('town');
            $oldProvinceId = optional($applicant->getOriginal('town'))->province_id;
            $newProvinceId = optional($applicant->town)->province_id;

            if (!is_null($oldProvinceId)) {
                self::adjustMonthlyData($yearlyDataId, 'Province', $oldProvinceId, false);
            }

            if (!is_null($newProvinceId)) {
                self::adjustMonthlyData($yearlyDataId, 'Province', $newProvinceId, true);
            }
        }
    }

    //Adjust Monthly Data
    protected static function adjustMonthlyData($yearlyDataId, $categoryType, $categoryId, $isIncrement)
    {
        $monthlyData = ApplicantMonthlyData::where([
            'applicant_total_data_id' => $yearlyDataId,
            'category_id' => $categoryId,
            'category_type' => $categoryType,
            'month' => now()->format('M')
        ])->first();

        if ($monthlyData) {
            $isIncrement ? $monthlyData->increment('count') : $monthlyData->decrement('count', 1, ['count' => 0]);
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
