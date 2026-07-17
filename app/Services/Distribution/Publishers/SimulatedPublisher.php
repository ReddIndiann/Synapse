<?php

namespace App\Services\Distribution\Publishers;

use App\Models\PublishJob;
use App\Models\UserPlatformAccount;
use App\Services\Distribution\Contracts\PlatformPublisher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SimulatedPublisher implements PlatformPublisher
{
    public function publish(PublishJob $job, ?UserPlatformAccount $account): array
    {
        $logs = [];
        $media = $job->mediaAsset;
        $channel = $job->distributionChannel;

        $logs[] = $this->log('Initializing simulated connector...');
        $logs[] = $this->log("Uploading to {$channel->name}...");
        $logs[] = $this->log('Verifying payload checksum...');

        $randomHash = Str::random(11);
        $publishedUrl = match (strtolower($channel->slug)) {
            'youtube' => "https://www.youtube.com/watch?v={$randomHash}",
            'spotify' => 'https://open.spotify.com/track/' . Str::random(22),
            'audiomack' => 'https://audiomack.com/song/synapse/' . Str::slug($media->title),
            default => "https://{$channel->slug}.com/synapse/" . Str::random(8),
        };

        $logs[] = $this->log("Published successfully: {$publishedUrl}");

        return [
            'published_url' => $publishedUrl,
            'external_post_id' => $randomHash,
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
