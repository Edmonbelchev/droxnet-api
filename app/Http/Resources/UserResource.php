<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'email'          => $this->email,
            'first_name'     => $this->first_name,
            'last_name'      => $this->last_name,
            'tagline'        => $this->tagline,
            'profile_image'  => $this->profile_image,
            'profile_banner' => $this->profile_banner,
            'hourly_rate'    => $this->hourly_rate,
            'gender'         => $this->gender,
            'country'        => $this->country,
            'city'           => $this->city,
            'phone'          => $this->phone,
            'date_of_birth'  => $this->date_of_birth,
            'about'          => $this->about,
            'role'           => $this->role->role->name,   
            'email_verified' => $this->email_verified_at !== null,
            'skills'         => UserSkillsResource::collection($this->whenLoaded('skills')),
            'educations'     => UserEducationResource::collection($this->whenLoaded('educations')),
            'experiences'    => UserExperienceResource::collection($this->whenLoaded('experiences')),
            'projects'       => UserProjectResource::collection($this->whenLoaded('projects')),
            'awards'         => UserAwardResource::collection($this->whenLoaded('awards')),
            'created_at'     => $this->created_at
        ];
    }
}
