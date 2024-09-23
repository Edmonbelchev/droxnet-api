<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SavedItem;

class SavedItemPolicy
{
    /**
     * Create a new policy instance.
     */
    public function delete(User $user, SavedItem $savedItem): bool
    {
        return $user->uuid === $savedItem->user_uuid;
    }
}
