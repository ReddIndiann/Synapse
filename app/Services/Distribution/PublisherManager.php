<?php

namespace App\Services\Distribution;

use App\Models\UserPlatformAccount;
use App\Services\Distribution\Contracts\PlatformPublisher;
use App\Services\Distribution\Publishers\FacebookPublisher;
use App\Services\Distribution\Publishers\LinkedInPublisher;
use App\Services\Distribution\Publishers\SimulatedPublisher;
use App\Services\Distribution\Publishers\SpotifyPublisher;
use App\Services\Distribution\Publishers\YouTubePublisher;
use InvalidArgumentException;

class PublisherManager
{
    /** @var array<string, class-string<PlatformPublisher>> */
    private array $publishers = [
        'youtube' => YouTubePublisher::class,
        'linkedin' => LinkedInPublisher::class,
        'facebook' => FacebookPublisher::class,
        'instagram' => FacebookPublisher::class,
        'spotify' => SpotifyPublisher::class,
        'audiomack' => SimulatedPublisher::class,
        'website' => SimulatedPublisher::class,
    ];

    public function resolve(string $channelSlug): PlatformPublisher
    {
        $slug = strtolower($channelSlug);
        $class = $this->publishers[$slug] ?? SimulatedPublisher::class;

        $publisher = app($class);
        if (!$publisher instanceof PlatformPublisher) {
            throw new InvalidArgumentException("Publisher [{$class}] must implement PlatformPublisher.");
        }

        return $publisher;
    }

    public function refreshTokenIfNeeded(?UserPlatformAccount $account): ?UserPlatformAccount
    {
        if (!$account || !$account->isTokenExpired()) {
            return $account;
        }

        return app(PlatformOAuthService::class)->refreshToken($account);
    }
}
