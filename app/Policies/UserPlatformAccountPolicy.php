<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserPlatformAccount;

class UserPlatformAccountPolicy
{
    public function view(User $user, UserPlatformAccount $account): bool
    {
        return $account->user_id === $user->id;
    }

    public function delete(User $user, UserPlatformAccount $account): bool
    {
        return $account->user_id === $user->id;
    }
}
