<?php

namespace App\Jobs;

use App\Events\PublishJobFailed;
use App\Events\PublishJobPublished;
use App\Models\PublishJob;
use App\Services\Distribution\PublisherManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ProcessPublishJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected PublishJob $publishJob) {}

    public function handle(PublisherManager $publisherManager): void
    {
        $job = $this->publishJob->fresh([
            'mediaAsset',
            'distributionChannel',
            'userPlatformAccount',
            'publishCampaign',
        ]);

        $logs = $job->logs ?: [];

        try {
            $logs[] = $this->logEntry('Initializing publishing connector...');
            $job->update(['status' => 'processing', 'logs' => $logs]);

            $account = $publisherManager->refreshTokenIfNeeded($job->userPlatformAccount);
            if ($account && $account->id !== $job->user_platform_account_id) {
                $job->update(['user_platform_account_id' => $account->id]);
            }

            $publisher = $publisherManager->resolve($job->distributionChannel->slug);
            $result = $publisher->publish($job, $account);

            $logs = array_merge($logs, $result['logs']);

            $job->update([
                'status' => 'published',
                'published_at' => Carbon::now(),
                'published_url' => $result['published_url'],
                'external_post_id' => $result['external_post_id'] ?? null,
                'logs' => $logs,
                'error_message' => null,
            ]);

            $job = $job->fresh();
            $job->user->notify(new \App\Notifications\PublishJobFinishedNotification($job, 'success'));
            PublishJobPublished::dispatch($job);

        } catch (\Throwable $e) {
            $logs[] = $this->logEntry('Failure occurred during publication: ' . $e->getMessage());
            $job->update([
                'status' => 'failed',
                'logs' => $logs,
                'error_message' => $e->getMessage(),
            ]);

            $job = $job->fresh();
            $job->user->notify(new \App\Notifications\PublishJobFinishedNotification($job, 'failed'));
            PublishJobFailed::dispatch($job);
        }
    }

    private function logEntry(string $message): array
    {
        return [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'formatted_time' => Carbon::now()->format('H:i:s'),
            'message' => $message,
        ];
    }
}
