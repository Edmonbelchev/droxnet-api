<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'uuid'            => $this->uuid,
            'email'           => $this->email,
            'first_name'      => $this->first_name,
            'last_name'       => $this->last_name,
            'tagline'         => $this->tagline,
            'profile_image'   => $this->profile_image,
            'profile_banner'  => $this->profile_banner,
            'hourly_rate'     => $this->hourly_rate,
            'gender'          => $this->gender,
            'country'         => $this->country,
            'city'            => $this->city,
            'phone'           => $this->phone,
            'date_of_birth'   => $this->date_of_birth,
            'about'           => $this->about,
            'role'            => $this->role,
            'email_verified'  => $this->email_verified_at !== null,
            'created_at'      => $this->created_at->toIso8601String()
        ];
    }
}
