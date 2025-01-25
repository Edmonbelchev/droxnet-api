<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPaymentMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_method_id' => $this->payment_method_id,
            'is_default' => (bool)$this->is_default,
            'brand' => $this->brand ?? null,
            'last4' => $this->last4 ?? null,
            'exp_month' => $this->exp_month ?? null,
            'exp_year' => $this->exp_year ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
