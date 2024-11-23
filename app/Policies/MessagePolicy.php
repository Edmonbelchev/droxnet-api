<?php

namespace App\Policies;

use App\Models\User;

class MessagePolicy
{
    /**
     * Determine if the user can create a new message.
     */
    // public function create(User $user, $receiverId): bool
    // {
    //     // Prevent user from sending a message to themselves
    //     return $user->uuid !== $receiverId;
    // }
}
