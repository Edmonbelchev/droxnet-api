<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProposalRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'job_id'      => 'required|exists:jobs,id',
            'subject'     => 'required|string|max:128',
            'description' => 'required|string|max:512',
            'price'       => 'required|numeric'
        ];
    }
}
