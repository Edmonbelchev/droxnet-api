<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobCommentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'comment' => 'required|string|max:512',
        ];
    }
}
