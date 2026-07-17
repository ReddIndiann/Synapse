<?php

namespace App\Services\Distribution\Publishers;

use App\Models\PublishJob;
use App\Models\UserPlatformAccount;
use App\Services\Distribution\Contracts\PlatformPublisher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SpotifyPublisher implements PlatformPublisher
{
    public function publish(PublishJob $job, ?UserPlatformAccount $account): array
    {
        if (!$account || !$account->access_token || !config('distribution.oauth.spotify.client_id')) {
            return app(SimulatedPublisher::class)->publish($job, $account);
        }

        $logs = [];
        $options = $job->platform_options ?? [];
        $showId = $options['show_id'] ?? ($account->metadata['show_id'] ?? null);

        if (!$showId) {
            $logs[] = $this->log('No Spotify show_id configured — using simulated upload.');
            return app(SimulatedPublisher::class)->publish($job, $account);
        }

        $logs[] = $this->log("Creating Spotify episode for show {$showId}...");

        $response = Http::withToken($account->access_token)->post("https://api.spotify.com/v1/shows/{$showId}/episodes", [
            'name' => $job->mediaAsset->title,
            'description' => $job->caption ?? '',
            'episode_type' => $options['episode_type'] ?? 'full',
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Spotify publish failed: ' . $response->body());
        }

        $episodeId = $response->json('id') ?? Str::random(22);
        $publishedUrl = "https://open.spotify.com/episode/{$episodeId}";

        $logs[] = $this->log("Spotify episode published: {$publishedUrl}");

        return [
            'published_url' => $publishedUrl,
            'external_post_id' => $episodeId,
            'logs' => $logs,
        ];
    }

    protected function log(string $message): array
    {
        return [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'formatted_time' => Carbon::now()->format('H:i:s'),
            'message' => $message,
        ];
    }
}
