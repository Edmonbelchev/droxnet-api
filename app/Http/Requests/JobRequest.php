<?php

namespace App\Http\Requests;

use App\JobTypeEnum;
use App\JobLevelEnum;
use App\JobDurationEnum;
use App\JobBudgetTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class JobRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    
    public function rules(): array
    {
        $jobDuration   = JobDurationEnum::toArray();
        $jobType       = JobTypeEnum::toArray();
        $jobBudgetType = JobBudgetTypeEnum::toArray();
        $jobLevel      = JobLevelEnum::toArray();

        return [
            'title'            => 'required|string|max:255',
            'description'      => 'required|string|max:4096',
            'budget'           => 'required|numeric',
            'duration'         => 'required|string|max:128|in:' . implode(',', $jobDuration),
            'location'         => 'required|string|max:128',
            'country'          => 'required|string|max:32',
            'type'             => 'required|string|max:64|in:' . implode(',', $jobType),
            'budget_type'      => 'required|string|max:64|in:' . implode(',', $jobBudgetType),
            'category_id'      => 'nullable|integer|exists:categories,id',
            'level'            => 'required|string|max:255|in:' . implode(',', $jobLevel),
            'languages'        => 'required|array',
            'skills'           => 'array|present',
            'files'            => 'array|present',
            'show_attachments' => 'boolean|present',
        ];
    }
}
