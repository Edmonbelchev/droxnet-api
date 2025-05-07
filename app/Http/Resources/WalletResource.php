<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_uuid' => $this->user->uuid,
            'pending_balance' => $this->pending_balance,
            'balance' => $this->balance,
            'escrow_balance' => $this->escrow_balance,
            'currency' => $this->currency,
            'stripe_customer_id' => $this->stripe_customer_id,
            'stripe_connect_id' => $this->stripe_connect_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
