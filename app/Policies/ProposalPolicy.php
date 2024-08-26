<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Proposal;

class ProposalPolicy
{
    public function show(User $user, Proposal $proposal): bool
    {
        return $user->uuid === $proposal->user_uuid || $user->uuid === $proposal->job->user_uuid;
    }

    public function create(User $user): bool
    {
        return $user->role === "freelancer";
    }

    public function update(User $user, Proposal $proposal): bool
    {
        return$user->role === "freelancer" && $user->uuid === $proposal->user_uuid;
    }
}
