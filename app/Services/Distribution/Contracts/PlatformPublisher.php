<?php

namespace App\Services\Distribution\Contracts;

use App\Models\PublishJob;
use App\Models\UserPlatformAccount;

interface PlatformPublisher
{
    /**
     * Publish media to the platform. Returns result with url and external id.
     *
     * @return array{published_url: string, external_post_id: ?string, logs: array<int, array{timestamp: string, formatted_time: string, message: string}>}
     */
    public function publish(PublishJob $job, ?UserPlatformAccount $account): array;
}
