<?php

namespace App\Policies;

use App\Models\PublishCampaign;
use App\Models\User;

class PublishCampaignPolicy
{
    public function view(User $user, PublishCampaign $campaign): bool
    {
        return $campaign->user_id === $user->id;
    }

    public function delete(User $user, PublishCampaign $campaign): bool
    {
        return $campaign->user_id === $user->id;
    }
}
