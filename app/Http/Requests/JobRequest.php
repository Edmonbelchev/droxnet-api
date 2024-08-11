<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'            => 'required|string|max:255',
            'description'      => 'required|string|max:1024',
            'budget'           => 'required|numeric',
            'duration'         => 'required|string|max:128',
            'location'         => 'required|string|max:128',
            'type'             => 'required|string|max:64',
            'category_id'      => 'nullable|integer|exists:categories,id',
            'level'            => 'required|string|max:255',
            'languages'        => 'required|array',
            'skills'           => 'array|present',
            'files'            => 'array|present',
            'show_attachments' => 'boolean|present',
        ];
    }
}
