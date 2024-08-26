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
            'id'               => $this->id,
            'title'            => $this->title,
            'description'      => $this->description,
            'budget'           => $this->budget,
            'duration'         => $this->duration->getLabeltext(),
            'location'         => $this->location,
            'country'          => $this->country,
            'type'             => $this->type->getLabelText(),
            'budget_type'      => $this->budget_type->getLabelText(),
            'level'            => $this->level->getLabelText(),
            'languages'        => $this->languages,
            'show_attachments' => $this->show_attachments,
            'skills'           => SkillResource::collection($this->whenLoaded('skills')),
            'files'            => FileResource::collection($this->whenLoaded('files')),
            'user'             => UserResource::make($this->whenLoaded('user')),
            'proposals'        => ProposalResource::collection($this->whenLoaded('proposals', function () {
                return $this->proposals->take(8);
            })),
            'proposals_count'  => $this->whenLoaded('proposals', function () {
                return $this->proposals->count();
            }),
            'created_at'       => $this->created_at
        ];
    }
}
