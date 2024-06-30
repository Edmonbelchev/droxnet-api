<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function create(User $user): bool
    {
        return $user->role->role_id === 2;
    }

    public function update(User $user, Job $job): bool
    {
        return $user->id === $job->user_id && $user->role->role_id === 2;
    }
}
