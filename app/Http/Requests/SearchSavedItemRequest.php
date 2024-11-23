<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchSavedItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type'          => ['required', 'string', 'in:user,job'],
            'is_company'    => ['present', 'string', 'in:true,false'],
            'per_page'      => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
