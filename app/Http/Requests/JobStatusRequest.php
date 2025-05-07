<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:proposal,ongoing,completed,cancelled',
        ];
    }

     /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $job = $this->route('job'); // Assuming the job is passed in the route

            if ($job && $job->status === $this->input('status')) {
                $validator->errors()->add('status', 'The job is already in this status.');
            }
        });
    }
}
