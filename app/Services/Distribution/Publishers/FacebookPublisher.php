<?php

namespace App\Services\Distribution\Publishers;

use App\Models\PublishJob;
use App\Models\UserPlatformAccount;
use App\Services\Distribution\Contracts\PlatformPublisher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FacebookPublisher implements PlatformPublisher
{
    public function publish(PublishJob $job, ?UserPlatformAccount $account): array
    {
        if (!$account || !$account->access_token || !config('distribution.oauth.facebook.client_id')) {
            return app(SimulatedPublisher::class)->publish($job, $account);
        }

        $logs = [];
        $pageId = $account->metadata['page_id'] ?? $account->external_account_id;
        $message = $job->caption ?? $job->mediaAsset->title;

        $logs[] = $this->log("Publishing to Meta page {$pageId}...");

        $response = Http::post("https://graph.facebook.com/v19.0/{$pageId}/feed", [
            'message' => $message,
            'access_token' => $account->access_token,
            'link' => url('/'),
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Facebook publish failed: ' . $response->body());
        }

        $postId = $response->json('id') ?? Str::random(12);
        $publishedUrl = "https://www.facebook.com/{$postId}";

        $logs[] = $this->log("Facebook post published: {$publishedUrl}");

        return [
            'published_url' => $publishedUrl,
            'external_post_id' => $postId,
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
