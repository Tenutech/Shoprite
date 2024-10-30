<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'avatar' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'], // Avatar validation
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'id_number' => ['required', 'string', 'max:13'],
            'phone' => ['required', 'string', 'max:191'],
            'location' => ['required', 'string'],
            'latitude' => ['required', function ($attribute, $value, $fail) {
                if (empty($value)) {
                    $fail('Please select a verified address from the Google suggestions.');
                }
            }],
            'longitude' => ['required', function ($attribute, $value, $fail) {
                if (empty($value)) {
                    $fail('Please select a verified address from the Google suggestions.');
                }
            }],
            'race_id' => ['required', 'integer', 'exists:races,id'],
            'email' => ['required', 'string', 'email', 'max:191', Rule::unique('applicants')->ignore($this->applicantID)],
            'education_id' => ['required', 'integer', 'exists:educations,id'],
            'duration_id' => ['required', 'integer', 'exists:durations,id'],
            'public_holidays' => ['required', 'in:Yes,No'],
            'environment' => ['required', 'in:Yes,No'],
            'brand_id' => ['sometimes', 'nullable', 'integer', 'exists:brands,id'],
            'disability' => ['required', 'in:Yes,No'],
        ];
    }

    /**
     * Prepare applicant id before validation
     *
     * @return void
     */
    public function prepareForValidation()
    {
        //Applicant ID
        $this->merge([
            'applicantID' => Crypt::decryptString($this->field_id),
        ]);
    }
}
