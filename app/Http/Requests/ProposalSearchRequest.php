<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProposalSearchRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'query'          => ['sometimes', 'string'],
            'per_page'       => ['sometimes', 'integer', 'min:1', 'max:100'],
            'job_id'         => ['sometimes', 'integer', 'exists:jobs,id'],
        ];
    }
}
