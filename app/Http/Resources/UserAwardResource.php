<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\FileResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAwardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'user_uuid' => $this->user_uuid,
            'title'     => $this->title,
            'date'      => $this->date,
            'files'     => FileResource::collection($this->files),
        ];
    }
}
