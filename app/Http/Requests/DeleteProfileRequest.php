<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class DeleteProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Require user password and confirm user password
            'password'         => 'required|string',
            'confirm_password' => 'required|string',
            'reason'           => 'required|string|max:255'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Get the currently authenticated user
            $user = auth()->user();

            // Check if the provided password matches the user's actual password
            if (!Hash::check($this->password, $user->password)) {
                $validator->errors()->add('password', 'The provided password is incorrect.');
            }

            // Check if the provided confirm password matches the password
            if ($this->password !== $this->confirm_password) {
                $validator->errors()->add('confirm_password', 'The provided confirm password does not match the password.');
            }
        });
    }
}
