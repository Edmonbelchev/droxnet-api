<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;
use App\Models\JobComment;

class JobCommentPolicy
{
    public function update(User $user, JobComment $jobComment): bool
    {
        return $user->uuid === $jobComment->user_uuid;
    }

    public function delete(User $user, JobComment $jobComment): bool
    {
        return $user->uuid === $jobComment->user_uuid;
    }
}
