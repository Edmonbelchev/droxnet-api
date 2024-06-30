<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'query'          => ['sometimes', 'string'],
            'per_page'       => ['sometimes', 'integer', 'min:1', 'max:100'],
            'excluded_skill' => ['sometimes', 'array'],
        ];
    }
}
