<?php

namespace App\Http\Resources\Dashboard;

use App\Http\Resources\JobResource;
use App\Http\Resources\ProposalResource;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this),
            'transactions' => TransactionResource::collection($this->wallet?->transactions ?? collect()),
            'jobs' => JobResource::collection($this->jobs),
            'proposals' => ProposalResource::collection($this->proposals),
        ];
    }
} 