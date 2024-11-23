<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
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
            'employer'   => new SimpleUserResource($this->employer),
            'freelancer' => new SimpleUserResource($this->freelancer),
            'messages'   => $this->whenLoaded('messages', function () {
                return new MessageCollection($this->messages);
            }),
            'last_message' => new MessageResource($this->last_message),
        ];
    }
}
