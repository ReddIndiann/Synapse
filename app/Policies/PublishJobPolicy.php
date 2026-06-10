<?php

namespace App\Policies;

use App\Models\PublishJob;
use App\Models\User;

class PublishJobPolicy
{
    public function view(User $user, PublishJob $publishJob): bool
    {
        return $publishJob->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PublishJob $publishJob): bool
    {
        return $publishJob->user_id === $user->id;
    }

    public function delete(User $user, PublishJob $publishJob): bool
    {
        return $publishJob->user_id === $user->id;
    }
}
