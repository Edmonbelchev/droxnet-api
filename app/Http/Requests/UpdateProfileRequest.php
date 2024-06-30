<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'tagline'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'profile_image'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'profile_banner' => ['sometimes', 'nullable', 'string', 'max:255'],
            'gender'         => ['required', Rule::in(['male', 'female'])],
            'phone'          => ['present', 'nullable', 'string', 'max:255'],
            'country'        => ['required', 'string', 'max:64'],
            'city'           => ['present', 'nullable', 'string', 'max:255'],
            'about'          => ['present', 'nullable', 'string'],
            'date_of_birth'  => ['present', 'nullable', 'date'],
            'hourly_rate'    => ['present', 'nullable', 'int', 'min:0'],
            'company_name'   => ['present', 'nullable', 'string', 'max:255'],
            'skills'         => ['present', 'nullable', 'array'],
            'skills.*.id'    => ['required', 'integer', 'exists:skills,id'],
            'skills.*.rate'  => ['required', 'integer', 'min:10', 'max:100'],
        ];
    }
}
