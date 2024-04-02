<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    // 'password'    => [
    //     'required', 'max:64', 'string',
    //     Password::min(8)->uncompromised()
    // ],
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name'  => ['required', 'string', 'max:64'],
            'last_name'   => ['string', 'max:64'],
            'email'       => [
                'required', 'email', 'max:128',
                Rule::unique(User::class, 'email')
            ],
            'password'    => [
                'required', 'max:64', 'string',
                Password::min(8)->uncompromised()
            ],
            // 'country'   => ['string', 'max:32'],
            // 'user_type' => ['required', 'string', Rule::in(['freelancer', 'employer'])],
        ];
    }
}
