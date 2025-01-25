<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function fundMilestone(User $user, Milestone $milestone): bool
    {
        // Only the job owner (employer) can fund a milestone
        return $user->id === $milestone->job->user_id;
    }

    public function releaseMilestonePayment(User $user, Milestone $milestone): bool
    {
        // Only the job owner (employer) can release the payment
        // The milestone must be completed and funded
        return $user->id === $milestone->job->user_id &&
            $milestone->status === Milestone::STATUS_COMPLETED;
    }

    public function createMilestone(User $user, Milestone $milestone): bool
    {
        // Only the job owner can create milestones
        return $user->id === $milestone->job->user_id;
    }
}
