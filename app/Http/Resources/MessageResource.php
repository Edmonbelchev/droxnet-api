<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'message'       => $this->message,
            'is_read'       => $this->is_read,
            'sender'        => new UserResource($this->sender),
            'updated_at'    => $this->updated_at->toIso8601String(),
            'created_at'    => $this->created_at->toIso8601String(),
        ];
    }
}
