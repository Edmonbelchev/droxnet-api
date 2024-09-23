<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function store(User $user): bool
    {
        return $user->role === "employer";
    }

    public function update(User $user, Job $job): bool
    {
        return $user->uuid === $job->user_uuid && $user->role === "employer";
    }

    public function updateStatus(User $user, Job $job): bool
    {
        return $user->uuid === $job->user_uuid;
    }

    public function manageComments(User $user, Job $job): bool
    {
        return $user->uuid === $job->user_uuid || $job->acceptedProposals()->where('user_uuid', $user->uuid)->exists();
    }
}
