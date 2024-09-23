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
            'id'            => $this->id,
            'user_uuid'     => $this->user_uuid,
            'subject'       => $this->subject,
            'description'   => $this->description,
            'status'        => $this->status,
            'price'         => $this->price,
            'duration'      => $this->duration,
            'duration_type' => $this->duration_type,
            'job'           => new JobResource($this->whenLoaded('job')),
            'user'          => new UserResource($this->whenLoaded('user')),
            'files'         => FileCollection::make($this->whenLoaded('files')),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
