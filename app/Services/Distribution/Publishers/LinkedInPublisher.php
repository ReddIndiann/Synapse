<?php

namespace App\Services\Distribution\Publishers;

use App\Models\PublishJob;
use App\Models\UserPlatformAccount;
use App\Services\Distribution\Contracts\PlatformPublisher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LinkedInPublisher implements PlatformPublisher
{
    public function publish(PublishJob $job, ?UserPlatformAccount $account): array
    {
        if (!$account || !$account->access_token || !config('distribution.oauth.linkedin.client_id')) {
            return app(SimulatedPublisher::class)->publish($job, $account);
        }

        $logs = [];
        $logs[] = $this->log('Posting share to LinkedIn...');

        $authorUrn = $account->metadata['author_urn'] ?? ('urn:li:person:' . $account->external_account_id);
        $text = $job->caption ?? $job->mediaAsset->title;

        $response = Http::withToken($account->access_token)->post('https://api.linkedin.com/v2/ugcPosts', [
            'author' => $authorUrn,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => ['text' => $text],
                    'shareMediaCategory' => 'ARTICLE',
                    'media' => [[
                        'status' => 'READY',
                        'originalUrl' => url('/'),
                        'title' => ['text' => $job->mediaAsset->title],
                    ]],
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('LinkedIn publish failed: ' . $response->body());
        }

        $postId = $response->json('id') ?? Str::random(12);
        $publishedUrl = 'https://www.linkedin.com/feed/update/' . urlencode($postId);

        $logs[] = $this->log("LinkedIn post published: {$publishedUrl}");

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
