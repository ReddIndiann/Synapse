<?php

namespace App\Services\Distribution\Publishers;

use App\Models\PublishJob;
use App\Models\UserPlatformAccount;
use App\Services\Distribution\Contracts\PlatformPublisher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class YouTubePublisher implements PlatformPublisher
{
    public function publish(PublishJob $job, ?UserPlatformAccount $account): array
    {
        $logs = [];
        $options = $job->platform_options ?? [];
        $media = $job->mediaAsset;

        if (!$account || !$account->access_token) {
            $logs[] = $this->log('No connected YouTube account — using simulated upload.');
            return app(SimulatedPublisher::class)->publish($job, $account);
        }

        if (!config('distribution.oauth.youtube.client_id')) {
            $logs[] = $this->log('YouTube OAuth not configured — using simulated upload.');
            return app(SimulatedPublisher::class)->publish($job, $account);
        }

        $logs[] = $this->log('Starting YouTube resumable upload...');

        $title = $options['title'] ?? $media->title;
        $privacy = $options['privacy'] ?? 'public';
        $tags = $options['tags'] ?? [];

        $initResponse = Http::withToken($account->access_token)
            ->withHeaders([
                'X-Upload-Content-Type' => $media->mime_type,
                'X-Upload-Content-Length' => (string) $media->size,
            ])
            ->post('https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status', [
                'snippet' => [
                    'title' => $title,
                    'description' => $job->caption ?? '',
                    'tags' => $tags,
                ],
                'status' => [
                    'privacyStatus' => $privacy,
                ],
            ]);

        if (!$initResponse->successful()) {
            throw new \RuntimeException('YouTube upload init failed: ' . $initResponse->body());
        }

        $uploadUrl = $initResponse->header('Location');
        if (!$uploadUrl) {
            throw new \RuntimeException('YouTube did not return an upload URL.');
        }

        $logs[] = $this->log('Upload session established. Streaming file...');

        $fileContents = Storage::disk('public')->get($media->path);
        $uploadResponse = Http::withHeaders([
            'Content-Type' => $media->mime_type,
            'Content-Length' => (string) strlen($fileContents),
        ])->withBody($fileContents, $media->mime_type)->put($uploadUrl);

        if (!$uploadResponse->successful()) {
            throw new \RuntimeException('YouTube upload failed: ' . $uploadResponse->body());
        }

        $videoId = $uploadResponse->json('id') ?? Str::random(11);
        $publishedUrl = "https://www.youtube.com/watch?v={$videoId}";

        $logs[] = $this->log("YouTube upload complete: {$publishedUrl}");

        return [
            'published_url' => $publishedUrl,
            'external_post_id' => $videoId,
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
