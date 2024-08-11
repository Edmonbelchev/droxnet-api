<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\CompanyDetailResource;
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
        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'tagline' => $this->tagline,
            'profile_image' => $this->profile_image,
            'profile_banner' => $this->profile_banner,
            'hourly_rate' => $this->hourly_rate,
            'gender' => $this->gender,
            'country' => $this->country,
            'city' => $this->city,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth ? $this->date_of_birth->toDateString() : null,
            'about' => $this->about,
            'role' => $this->role,
            'email_verified' => $this->email_verified_at !== null,
            'skills' => UserSkillsResource::collection($this->whenLoaded('skills')),
            'created_at' => $this->created_at
        ];

        if ($this->role === 'employer' && $this->relationLoaded('companyDetail')) {
            $data['company_details'] = new CompanyDetailResource($this->companyDetail);
        }

        return $data;
    }
}
