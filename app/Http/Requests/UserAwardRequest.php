<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAwardRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'awards'         => 'present|array',
            'awards.*.id'    => 'nullable|integer',
            'awards.*.date'  => 'required|date',
            'awards.*.title' => 'required|string|max:255',
            'awards.*.files' => 'array|present'
        ];
    }
}
