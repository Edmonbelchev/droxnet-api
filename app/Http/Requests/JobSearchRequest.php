<?php

namespace App\Http\Requests;

use App\JobDurationEnum;
use Illuminate\Foundation\Http\FormRequest;

class JobSearchRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Decode the JSON string if it exists
        if ($this->has('budget')) {
            $hourlyRate = json_decode($this->input('budget'), true);
            $this->merge([
                'budget' => $hourlyRate,
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
        $jobDuration   = JobDurationEnum::toArray();
        
        return [
            'countries'    => ['sometimes', 'nullable', 'array'],
            'countries.*'  => ['required', 'string', 'max:3'],
            'skills'       => ['sometimes', 'nullable', 'array'],
            'skills.*'     => ['required', 'integer', 'exists:skills,id'],
            'budget_type'  => ['sometimes', 'nullable', 'string', 'in:hourly,fixed,any'],
            'budget'       => ['sometimes', 'nullable', 'array'],
            'budget.start' => ['required_with:budget', 'integer', 'min:0'],
            'budget.end'   => ['required_with:budget', 'integer', 'min:0'],
            'duration'     => ['sometimes', 'nullable', 'array'],
            'duration.*'   => ['required', 'string', 'in:' . implode(',', $jobDuration)],
        ];
    }
}

