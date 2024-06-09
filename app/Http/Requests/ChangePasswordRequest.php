<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password'     => ['required', 'max:64', 'string', Password::min(8)->uncompromised()]
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

            // Check if the provided current password matches the user's actual password
            if (!Hash::check($this->current_password, $user->password)) {
                $validator->errors()->add('current_password', 'The current password is incorrect.');
            }

            // Check if the new password is different from the current password
            if ($this->current_password === $this->new_password) {
                $validator->errors()->add('new_password', 'New password must be different from the current password.');
            }
        });
    }
}
