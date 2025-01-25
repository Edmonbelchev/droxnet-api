<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->wallet_id,
            'job_id' => $this->job_id,
            'type' => $this->type,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'stripe_payment_intent_id' => $this->metadata['payment_intent_id'] ?? null,
            'stripe_transfer_id' => $this->stripe_transfer_id,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
