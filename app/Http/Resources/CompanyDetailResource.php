<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'company_name'   => $this->company_name,
            'company_website' => $this->company_website,
            'company_size'   => $this->company_size,
            'department'     => $this->department
        ];
    }
}
