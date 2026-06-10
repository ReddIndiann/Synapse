<?php

namespace App\Policies;

use App\Models\MediaAsset;
use App\Models\User;

class MediaAssetPolicy
{
    public function view(User $user, MediaAsset $mediaAsset): bool
    {
        return $mediaAsset->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, MediaAsset $mediaAsset): bool
    {
        return $mediaAsset->user_id === $user->id;
    }

    public function delete(User $user, MediaAsset $mediaAsset): bool
    {
        return $mediaAsset->user_id === $user->id;
    }
}
