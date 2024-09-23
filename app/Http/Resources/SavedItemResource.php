<?php

namespace App\Http\Resources;

use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavedItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $saveable = $this->saveable;
        $saveableResponse = null;

        if ($this->saveable_type === 'user') {
            $saveable = User::where('id', $this->saveable_id)->first();
            if ($saveable) {
                $saveableResponse = new UserResource($saveable);
            }
        } else if ($saveable instanceof Job) {
            $saveableResponse = new JobResource($saveable);
        }

        return [
            'id'            => $this->id,
            'user_uuid'     => $this->user_uuid,
            'saveable_id'   => $this->saveable_id,
            'saveable_type' => $this->saveable_type,
            'data'          => $saveableResponse,
            'created_at'    => $this->created_at->toIso8601String()
        ];
    }
}
