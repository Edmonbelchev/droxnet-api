<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayoutRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'interval' => 'required|in:manual,daily,weekly,monthly',
            'weekly_anchor' => 'required_if:interval,weekly|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'monthly_anchor' => 'required_if:interval,monthly|integer|between:1,31'
        ];
    }
}
