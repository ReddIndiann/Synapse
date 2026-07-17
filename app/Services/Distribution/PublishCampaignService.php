<?php

namespace App\Services\Distribution;

use App\Models\DistributionChannel;
use App\Models\MediaAsset;
use App\Models\PublishCampaign;
use App\Models\PublishCostRule;
use App\Models\PublishJob;
use App\Models\UserPlatformAccount;
use App\Jobs\ProcessPublishJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PublishCampaignService
{
    public function createCampaign(int $userId, array $data): PublishCampaign
    {
        return DB::transaction(function () use ($userId, $data) {
            $channelIds = $data['distribution_channel_ids'];
            $scheduledAt = !empty($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null;
            $isScheduled = $scheduledAt !== null;

            $campaign = PublishCampaign::create([
                'user_id' => $userId,
                'media_asset_id' => $data['media_asset_id'],
                'caption' => $data['caption'] ?? null,
                'scheduled_at' => $scheduledAt,
                'status' => $isScheduled ? 'queued' : 'queued',
                'record_cost' => (bool) ($data['record_cost'] ?? false),
                'estimated_cost_per_channel' => $data['estimated_cost_per_channel'] ?? null,
                'currency' => $data['currency'] ?? 'GHS',
            ]);

            $channels = DistributionChannel::query()
                ->whereIn('id', $channelIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            $immediateJobs = [];

            foreach ($channelIds as $channelId) {
                $channel = $channels->get($channelId);
                if (!$channel) {
                    continue;
                }

                $account = $this->resolveAccount($userId, $channel->id);
                $platformOptions = $this->buildPlatformOptions($channel->slug, $data, $campaign->mediaAsset);

                $job = PublishJob::create([
                    'user_id' => $userId,
                    'publish_campaign_id' => $campaign->id,
                    'media_asset_id' => $campaign->media_asset_id,
                    'distribution_channel_id' => $channel->id,
                    'user_platform_account_id' => $account?->id,
                    'status' => $isScheduled ? 'scheduled' : 'pending',
                    'caption' => $campaign->caption,
                    'platform_options' => $platformOptions,
                    'scheduled_at' => $scheduledAt,
                ]);

                if (!$isScheduled) {
                    $immediateJobs[] = $job;
                }
            }

            foreach ($immediateJobs as $job) {
                ProcessPublishJob::dispatch($job);
            }

            return $campaign->load('publishJobs.distributionChannel');
        });
    }

    public function createFromAi(int $userId, MediaAsset $media, array $channelSlugs, ?string $caption = null, ?Carbon $scheduledAt = null): PublishCampaign
    {
        $channels = DistributionChannel::query()
            ->whereIn('slug', $channelSlugs)
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        if (empty($channels)) {
            $channels = DistributionChannel::query()->where('is_active', true)->limit(1)->pluck('id')->all();
        }

        return $this->createCampaign($userId, [
            'media_asset_id' => $media->id,
            'distribution_channel_ids' => $channels,
            'caption' => $caption ?? 'Published via AI assistant',
            'scheduled_at' => $scheduledAt?->toDateTimeString(),
            'record_cost' => false,
        ]);
    }

    private function resolveAccount(int $userId, int $channelId): ?UserPlatformAccount
    {
        return UserPlatformAccount::query()
            ->where('user_id', $userId)
            ->where('distribution_channel_id', $channelId)
            ->where('is_active', true)
            ->first();
    }

    private function buildPlatformOptions(string $slug, array $data, MediaAsset $media): array
    {
        $options = $data['platform_options'][$slug] ?? [];

        return match ($slug) {
            'youtube' => array_merge([
                'title' => $options['title'] ?? $media->title,
                'tags' => $options['tags'] ?? [],
                'privacy' => $options['privacy'] ?? 'public',
            ], $options),
            'spotify' => array_merge([
                'show_id' => $options['show_id'] ?? null,
                'episode_type' => $options['episode_type'] ?? 'full',
            ], $options),
            default => $options,
        };
    }

    public function defaultCostForChannel(int $userId, DistributionChannel $channel): float
    {
        $rule = PublishCostRule::query()
            ->where('user_id', $userId)
            ->where('distribution_channel_id', $channel->id)
            ->first();

        if ($rule) {
            return (float) $rule->default_cost;
        }

        return (float) (config("distribution.default_costs.{$channel->slug}") ?? 25);
    }
}
