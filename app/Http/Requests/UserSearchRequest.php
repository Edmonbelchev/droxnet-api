<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserSearchRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Decode the JSON string if it exists
        if ($this->has('hourly_rate')) {
            $hourlyRate = json_decode($this->input('hourly_rate'), true);
            $this->merge([
                'hourly_rate' => $hourlyRate,
            ]);
        }
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'countries'         => ['sometimes', 'nullable', 'array'],
            'countries.*'       => ['required', 'string', 'max:3'],
            'skills'            => ['sometimes', 'nullable', 'array'],
            'skills.*'          => ['required', 'integer', 'exists:skills,id'],
            'hourly_rate'       => ['sometimes', 'nullable', 'array'],
            'hourly_rate.start' => ['required_with:hourly_rate', 'integer', 'min:0'],
            'hourly_rate.end'   => ['required_with:hourly_rate', 'integer', 'min:0'],
        ];
    }
}
