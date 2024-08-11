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
        ];

        if ($this->user()->role === 'freelancer') {
            $rules['skills'] = ['required', 'array', 'min:1'];
            $rules['skills.*.id'] = ['required', 'integer', 'exists:skills,id'];
            $rules['skills.*.rate'] = ['required', 'integer', 'min:10', 'max:100'];
        } else {
            $rules['skills'] = ['present', 'nullable', 'array'];
            $rules['skills.*.id'] = ['required', 'integer', 'exists:skills,id'];
            $rules['skills.*.rate'] = ['required', 'integer', 'min:10', 'max:100'];
        }

        if ($this->user()->role === 'employer') {
            $rules['company_details'] = ['required', 'array'];
            $rules['company_details.company_name'] = ['required', 'string', 'max:128'];
            $rules['company_details.company_website'] = ['required', 'string', 'max:255', 'url'];
            $rules['company_details.company_size'] = ['required', 'string', 'max:64'];
            $rules['company_details.department'] = ['required', 'string', 'max:128'];
        } else {
            $rules['company_details'] = ['sometimes', 'nullable', 'array'];
            $rules['company_details.company_name'] = ['sometimes', 'nullable', 'string', 'max:128'];
            $rules['company_details.company_website'] = ['sometimes', 'nullable', 'string', 'max:255', 'url'];
            $rules['company_details.company_size'] = ['sometimes', 'nullable', 'string', 'max:64'];
            $rules['company_details.department'] = ['sometimes', 'nullable', 'string', 'max:128'];
        }

        return $rules;
    }
}
