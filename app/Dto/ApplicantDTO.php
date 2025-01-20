<?php

namespace App\DTO;

class ApplicantDTO
{
    public $createdAt;
    public $idNumber;
    public $firstName;
    public $lastName;
    public $birthDate;
    public $age;
    public $gender;
    public $race;
    public $phone;
    public $email;
    public $education;
    public $duration;
    public $town;
    public $province;
    public $brands;
    public $location;
    public $locationType;
    public $termsConditions;
    public $publicHolidays;
    public $environment;
    public $consent;
    public $disability;
    public $literacyPercentage;
    public $numeracyPercentage;
    public $situationalPercentage;
    public $assessmentScore;
    public $score;
    public $applicationType;
    public $dropOff;
    public $stateName;
    public $appointed;
    public $sapNumber;

    public function __construct($data)
    {
        $this->createdAt = $data->created_at->format('Y-m-d H:i');
        $this->idNumber = $data->id_number ?? '';
        $this->firstName = $data->firstname ?? '';
        $this->lastName = $data->lastname ?? '';
        $this->birthDate = $data->birth_date ? date('Y-m-d', strtotime($data->birth_date)) : '';
        $this->age = $data->age ?? '';
        $this->gender = $data->gender_name ?? '';
        $this->race = $data->race_name ?? '';
        $this->phone = $data->phone ?? '';
        $this->email = $data->email ?? '';
        $this->education = $data->education_name ?? '';
        $this->duration = $data->duration_name ?? '';
        $this->town = $data->town_name ?? '';
        $this->province = $data->province_name ?? '';
        $this->brands = $data->brand_names ?? '';
        $this->location = $data->location ?? '';
        $this->locationType = $data->location_type ?? '';
        $this->termsConditions = $data->terms_conditions ?? '';
        $this->publicHolidays = $data->public_holidays;
        $this->environment = $data->environment;
        $this->consent = $data->consent ?? '';
        $this->disability = $data->disability ?? '';
        $this->literacyPercentage = ($data->literacy_questions ?? 0) > 0
            ? round(($data->literacy_score / $data->literacy_questions) * 100)
            : '';
        $this->numeracyPercentage = ($data->numeracy_questions ?? 0) > 0
            ? round(($data->numeracy_score / $data->numeracy_questions) * 100)
            : '';
        $this->situationalPercentage = ($data->situational_questions ?? 0) > 0
            ? round(($data->situational_score / $data->situational_questions) * 100)
            : '';
        $totalQuestions = ($data->literacy_questions ?? 0) +
            ($data->numeracy_questions ?? 0) +
            ($data->situational_questions ?? 0);
        $totalScore = ($data->literacy_score ?? 0) +
            ($data->numeracy_score ?? 0) +
            ($data->situational_score ?? 0);
        $this->assessmentScore = $totalQuestions > 0 ? round(($totalScore / $totalQuestions) * 100) : '';
        $this->score = $data->score ?? '';
        $this->applicationType = $data->application_type ?? '';
        $this->dropOff = $data->state_id < $data->completeStateID ? 'Yes' : 'No';
        $this->stateName = $data->state_name ?? '';
        $this->appointed = $data->appointed_id ? 'Yes' : 'No';
        $this->sapNumber = $data->latest_sap_number ?? '';
    }
}