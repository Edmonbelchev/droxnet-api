<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserEducationResource extends JsonResource
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
            'school_name'   => $this->school_name,
            'degree'        => $this->degree,
            'field_of_study'=> $this->field_of_study,
            'start_date'    => $this->start_date->toDateString(),
            'end_date'      => $this->end_date ? $this->end_date->toDateString() : null,
            'description'   => $this->description,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
