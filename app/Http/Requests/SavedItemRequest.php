<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Job;
use Illuminate\Foundation\Http\FormRequest;

class SavedItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'saveable_type' => ['required', 'string', 'in:user,job'],
            'saveable_id'   => ['required', 'integer'],
        ];
    }

    // Add a custom validation rule to check if the saveable_id exists in the database
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->saveable_type === 'user') {
                if($this->saveable_id == auth()->user()->id) {
                    $validator->errors()->add('saveable_id', 'This action is not allowed.');
                }

                $user = User::where('id', $this->saveable_id)->first();
                if (!$user) {
                    $validator->errors()->add('saveable_id', 'The selected user does not exist.');
                }
            } else if ($this->saveable_type === 'job') {
                $job = Job::find($this->saveable_id);
                if (!$job) {
                    $validator->errors()->add('saveable_id', 'The selected job does not exist.');
                }
            }
        });
    }
}
