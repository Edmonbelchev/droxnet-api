<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserEducationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'educations'                  => 'present|array',
            'educations.*.id'             => 'nullable|integer',
            'educations.*.school_name'    => 'required|string|max:255',
            'educations.*.degree'         => 'required|string|max:255',
            'educations.*.field_of_study' => 'required|string|max:255',
            'educations.*.start_date'     => 'required|date',
            'educations.*.end_date'       => 'nullable|date|after_or_equal:educations.*.start_date',
            'educations.*.description'    => 'nullable|string',
        ];
    }
}
