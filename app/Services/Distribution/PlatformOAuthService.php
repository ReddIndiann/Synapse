<?php

namespace App\Services\Distribution;

use App\Models\DistributionChannel;
use App\Models\UserPlatformAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class PlatformOAuthService
{
    /** @var array<string, array{auth_url: string, token_url: string, user_url?: string}> */
    private array $providers = [
        'youtube' => [
            'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'user_url' => 'https://www.googleapis.com/youtube/v3/channels?part=snippet&mine=true',
        ],
        'spotify' => [
            'auth_url' => 'https://accounts.spotify.com/authorize',
            'token_url' => 'https://accounts.spotify.com/api/token',
            'user_url' => 'https://api.spotify.com/v1/me',
        ],
        'facebook' => [
            'auth_url' => 'https://www.facebook.com/v19.0/dialog/oauth',
            'token_url' => 'https://graph.facebook.com/v19.0/oauth/access_token',
            'user_url' => 'https://graph.facebook.com/v19.0/me?fields=id,name',
        ],
        'instagram' => [
            'auth_url' => 'https://www.facebook.com/v19.0/dialog/oauth',
            'token_url' => 'https://graph.facebook.com/v19.0/oauth/access_token',
            'user_url' => 'https://graph.facebook.com/v19.0/me/accounts',
        ],
        'linkedin' => [
            'auth_url' => 'https://www.linkedin.com/oauth/v2/authorization',
            'token_url' => 'https://www.linkedin.com/oauth/v2/accessToken',
            'user_url' => 'https://api.linkedin.com/v2/userinfo',
        ],
    ];

    public function getAuthorizationUrl(DistributionChannel $channel): string
    {
        $slug = strtolower($channel->slug);
        $config = config("distribution.oauth.{$slug}");
        $provider = $this->providers[$slug] ?? null;

        if (!$config || !$provider || empty($config['client_id'])) {
            throw new \RuntimeException("OAuth is not configured for {$channel->name}.");
        }

        $state = Str::random(40);
        Session::put("oauth_state.{$slug}", $state);
        Session::put("oauth_channel.{$slug}", $channel->id);

        $redirectUri = $this->redirectUri($slug);

        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'state' => $state,
            'scope' => implode(' ', $config['scopes'] ?? []),
        ];

        if ($slug === 'youtube') {
            $params['access_type'] = 'offline';
            $params['prompt'] = 'consent';
        }

        return $provider['auth_url'] . '?' . http_build_query($params);
    }

    public function handleCallback(string $slug, string $code, string $state, int $userId): UserPlatformAccount
    {
        $expectedState = Session::pull("oauth_state.{$slug}");
        $channelId = Session::pull("oauth_channel.{$slug}");

        if (!$expectedState || !hash_equals($expectedState, $state)) {
            throw new \RuntimeException('Invalid OAuth state.');
        }

        $channel = DistributionChannel::findOrFail($channelId);
        $provider = $this->providers[$slug] ?? null;
        $config = config("distribution.oauth.{$slug}");

        if (!$provider || !$config) {
            throw new \RuntimeException("OAuth provider not found for {$slug}.");
        }

        $tokenResponse = Http::asForm()->post($provider['token_url'], [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri($slug),
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
        ]);

        if (!$tokenResponse->successful()) {
            throw new \RuntimeException('Token exchange failed: ' . $tokenResponse->body());
        }

        $accessToken = $tokenResponse->json('access_token');
        $refreshToken = $tokenResponse->json('refresh_token');
        $expiresIn = $tokenResponse->json('expires_in');

        $profile = $this->fetchProfile($slug, $provider, $accessToken);

        return UserPlatformAccount::updateOrCreate(
            [
                'user_id' => $userId,
                'distribution_channel_id' => $channel->id,
            ],
            [
                'external_account_id' => $profile['id'],
                'account_name' => $profile['name'],
                'account_handle' => $profile['handle'] ?? null,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_expires_at' => $expiresIn ? Carbon::now()->addSeconds($expiresIn) : null,
                'scopes' => $config['scopes'] ?? [],
                'metadata' => $profile['metadata'] ?? [],
                'is_active' => true,
                'last_synced_at' => Carbon::now(),
            ]
        );
    }

    public function refreshToken(UserPlatformAccount $account): ?UserPlatformAccount
    {
        if (!$account->refresh_token) {
            return $account;
        }

        $slug = strtolower($account->distributionChannel->slug);
        $provider = $this->providers[$slug] ?? null;
        $config = config("distribution.oauth.{$slug}");

        if (!$provider || !$config) {
            return $account;
        }

        $response = Http::asForm()->post($provider['token_url'], [
            'grant_type' => 'refresh_token',
            'refresh_token' => $account->refresh_token,
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
        ]);

        if (!$response->successful()) {
            $account->update(['is_active' => false]);
            return $account;
        }

        $account->update([
            'access_token' => $response->json('access_token'),
            'refresh_token' => $response->json('refresh_token') ?? $account->refresh_token,
            'token_expires_at' => $response->json('expires_in')
                ? Carbon::now()->addSeconds($response->json('expires_in'))
                : $account->token_expires_at,
            'last_synced_at' => Carbon::now(),
        ]);

        return $account->fresh();
    }

    public function disconnect(UserPlatformAccount $account): void
    {
        $account->update([
            'is_active' => false,
            'access_token' => null,
            'refresh_token' => null,
        ]);
    }

    public function requiresOAuth(DistributionChannel $channel): bool
    {
        return in_array(strtolower($channel->slug), config('distribution.requires_oauth', []), true);
    }

    public function isConfigured(DistributionChannel $channel): bool
    {
        $config = config('distribution.oauth.' . strtolower($channel->slug));
        return !empty($config['client_id']) && !empty($config['client_secret']);
    }

    private function redirectUri(string $slug): string
    {
        $path = config("distribution.oauth.{$slug}.redirect", "/distribution/accounts/callback/{$slug}");

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return url($path);
    }

    private function fetchProfile(string $slug, array $provider, string $accessToken): array
    {
        if (empty($provider['user_url'])) {
            return ['id' => Str::random(10), 'name' => ucfirst($slug) . ' Account'];
        }

        $response = Http::withToken($accessToken)->get($provider['user_url']);

        if (!$response->successful()) {
            return ['id' => Str::random(10), 'name' => ucfirst($slug) . ' Account'];
        }

        return match ($slug) {
            'youtube' => [
                'id' => $response->json('items.0.id') ?? Str::random(10),
                'name' => $response->json('items.0.snippet.title') ?? 'YouTube Channel',
                'handle' => $response->json('items.0.snippet.customUrl'),
                'metadata' => ['channel_id' => $response->json('items.0.id')],
            ],
            'spotify' => [
                'id' => $response->json('id') ?? Str::random(10),
                'name' => $response->json('display_name') ?? 'Spotify Account',
                'handle' => $response->json('id'),
            ],
            'linkedin' => [
                'id' => $response->json('sub') ?? Str::random(10),
                'name' => $response->json('name') ?? 'LinkedIn Profile',
                'metadata' => ['author_urn' => 'urn:li:person:' . ($response->json('sub') ?? Str::random(10))],
            ],
            default => [
                'id' => $response->json('id') ?? Str::random(10),
                'name' => $response->json('name') ?? ucfirst($slug) . ' Account',
            ],
        };
    }
}
