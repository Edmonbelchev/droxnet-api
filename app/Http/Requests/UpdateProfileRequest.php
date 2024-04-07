<?php

namespace App\Http\Requests;

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
            'first_name'    => ['required', 'string', 'max:255'],
            'last_name'     => ['required', 'string', 'max:255'],
            'phone'         => ['present', 'nullable', 'string', 'max:255'],
            'country'       => ['required', 'string', 'max:255'],
            'city'          => ['present', 'nullable', 'string', 'max:255'],
            'about'         => ['present', 'nullable', 'string'],
            'date_of_birth' => ['present', 'nullable', 'date'],
            'profile_image' => ['present', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'company_name'  => ['present', 'nullable', 'string', 'max:255'],
        ];
    }
}
