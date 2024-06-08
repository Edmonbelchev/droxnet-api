<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserExperienceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'experiences'                => 'present|array',
            'experiences.*.id'           => 'nullable|integer',
            'experiences.*.company_name' => 'required|string|max:255',
            'experiences.*.job_title'    => 'required|string|max:255',
            'experiences.*.start_date'   => 'required|date',
            'experiences.*.end_date'     => 'nullable|date|after_or_equal:experiences.*.start_date',
            'experiences.*.description'  => 'nullable|string',
        ];
    }
}
