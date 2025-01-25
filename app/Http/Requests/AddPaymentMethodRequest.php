<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddPaymentMethodRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'payment_method_id' => 'required|string|starts_with:pm_'
        ];
    }
}
