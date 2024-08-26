<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'subject'     => $this->subject,
            'description' => $this->description,
            'status'      => $this->status,
            'price'       => $this->price,
            'job'         => new JobResource($this->whenLoaded('job')),
            'user'        => new UserResource($this->whenLoaded('user')),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
