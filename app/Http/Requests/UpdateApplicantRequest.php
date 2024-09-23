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
            'avatar' => ['image' ,'mimes:jpg,jpeg,png','max:1024'],
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'email' => ['required', 'string', 'email', 'max:191', Rule::unique('applicants', 'email')->ignore($this->applicantID)],
            'phone' => ['required', 'string', 'max:191', Rule::unique('applicants', 'phone')->ignore($this->applicantID)],
            'id_number' => ['required', 'string',  'digits:13', Rule::unique('applicants')->ignore($this->applicantID)],
            'id_verified' => ['sometimes', 'nullable', 'string', 'in:Yes,No'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'age' => ['sometimes', 'nullable', 'integer', 'min:16', 'max:100'],
            'gender_id' => ['sometimes', 'nullable', 'integer', 'exists:genders,id'],
            'resident' => ['sometimes', 'nullable', 'integer', 'in:0,1'],
            'position_id' => ['sometimes', 'nullable', 'integer', 'exists:positions,id'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'store_id' => ['sometimes', 'nullable', 'integer', 'exists:stores,id'],
            'internal' => ['sometimes', 'nullable', 'integer', 'in:0,1']
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
