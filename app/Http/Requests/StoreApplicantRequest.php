<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicantRequest extends FormRequest
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
            'avatar' => ['image' ,'mimes:jpg,jpeg,png','max:1024'],
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:applicants'],
            'phone' => ['required', 'string', 'max:191', 'unique:applicants'],
            'id_number' => ['required', 'string',  'digits:13', 'unique:applicants'],
            'id_verified' => ['sometimes', 'nullable', 'string', 'in:Yes,No'],
            'disability' => ['sometimes', 'nullable', 'string', 'in:Yes,No'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'town_id' => ['sometimes', 'nullable', 'integer', 'exists:towns,id'],
            'age' => ['sometimes', 'nullable', 'integer', 'min:16', 'max:100'],
            'gender_id' => ['sometimes', 'nullable', 'integer', 'exists:genders,id'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'state_id' => ['sometimes', 'nullable', 'integer', 'exists:states,id'],
            'race_id' => ['sometimes', 'nullable', 'integer', 'exists:races,id'],
            'education_id' => ['sometimes', 'nullable', 'integer', 'exists:educations,id'],
            'duration_id' => ['sometimes', 'nullable', 'integer', 'exists:durations,id'],
            'applicant_type_id' => ['sometimes', 'nullable', 'integer', 'exists:applicant_types,id'],
            'application_type' => ['sometimes', 'nullable', 'string', 'in:Website,WhatsApp'],
            'no_show' => ['sometimes', 'nullable', 'integer', 'in:1,2,3'],
        ];
    }
}
