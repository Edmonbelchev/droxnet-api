<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'user'       => $this->whenLoaded('user', UserResource::make($this->user)),
            'comment'    => $this->comment,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
