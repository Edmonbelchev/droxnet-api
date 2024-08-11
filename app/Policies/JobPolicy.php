<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function create(User $user): bool
    {
        return $user->role === "employer";
    }

    public function update(User $user, Job $job): bool
    {
        return $user->uuid === $job->user_uuid && $user->role->role_id === "employer";
    }
}
