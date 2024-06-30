<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
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
            'title'       => $this->title,
            'description' => $this->description,
            'budget'      => $this->budget,
            'duration'    => $this->duration,
            'location'    => $this->location,
            'type'        => $this->type,
            'level'       => $this->level,
            'languages'   => $this->languages,
            'skills'      => SkillResource::collection($this->whenLoaded('skills')),
            'files'       => FileResource::collection($this->whenLoaded('files')),
            'user'        => UserResource::make($this->user),
            'created_at'  => $this->created_at
        ];
    }
}
