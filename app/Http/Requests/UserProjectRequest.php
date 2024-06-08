<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserProjectRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'projects'         => 'present|array',
            'projects.*.id'    => 'nullable|integer',
            'projects.*.url'   => 'required|string|max:255',
            'projects.*.title' => 'required|string|max:255',
            'projects.*.files' => 'array|present'
        ];
    }
}
