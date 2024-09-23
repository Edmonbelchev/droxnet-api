<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportSearchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status'   => ['sometimes', 'string', 'in:pending,approved,rejected'],
            'per_page' => ['sometimes', 'integer', 'min:1'],
            'type'     => ['sometimes', 'string', 'in:user,job'],
        ];
    }
}
