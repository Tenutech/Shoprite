<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignatureFileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => 'required|file|mimes:pdf|max:10240',
            'signers' => 'required|array',
            'signers.*.email' => 'required|email',
        ];
    }
}