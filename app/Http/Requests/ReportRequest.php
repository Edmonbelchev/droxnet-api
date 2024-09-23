<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Job;
use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reportable_type' => ['required', 'string', 'in:user,job'],
            'reportable_id'   => ['required', 'integer'],
            'reason'          => ['required', 'string', 'in:spam,scam,other'],
            'description'     => ['required', 'string', 'max:255'],
        ];
    }

    // Add a custom validation rule to check if the reportable_id exists in the database
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->reportable_type === 'user') {
                $user = User::where('id', $this->reportable_id)->first();

                if ($this->reportable_id == auth()->user()->id) {
                    $validator->errors()->add('reportable_id', 'This action is not allowed.');
                }

                if (!$user) {
                    $validator->errors()->add('reportable_id', 'The selected user does not exist.');
                }
            } else if ($this->reportable_type === 'job') {
                $job = Job::find($this->reportable_id);
                if (!$job) {
                    $validator->errors()->add('reportable_id', 'The selected job does not exist.');
                }
            }
        });
    }
}
