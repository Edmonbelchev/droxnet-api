<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'profile_image'  => $this->profile_image,
            'profile_banner' => $this->profile_banner,
            'hourly_rate'    => $this->hourly_rate,
            'gender'         => $this->gender,
            'country'        => $this->country,
            'city'           => $this->city,
            'phone'          => $this->phone,
            'date_of_birth'  => $this->date_of_birth ? $this->date_of_birth->toDateString() : null,
            'about'          => $this->about,
            'role'           => $this->role->role->name,   
            'email_verified' => $this->email_verified_at !== null,
            'skills'         => UserSkillsResource::collection($this->whenLoaded('skills'))
        ];
    }
}
