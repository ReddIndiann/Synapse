<?php

namespace App\Listeners;

use App\Events\PublishJobFailed;
use App\Events\PublishJobPublished;
use App\Models\PublishCampaign;
use App\Notifications\PublishCampaignFinishedNotification;

class NotifyBatchComplete
{
    public function handle(PublishJobPublished|PublishJobFailed $event): void
    {
        $job = $event->publishJob->fresh(['publishCampaign.publishJobs']);
        $campaign = $job->publishCampaign;

        if (!$campaign) {
            return;
        }

        $jobs = $campaign->publishJobs;
        $terminal = $jobs->whereIn('status', ['published', 'failed'])->count();

        if ($terminal < $jobs->count()) {
            $campaign->refreshStatus();
            return;
        }

        $campaign->refreshStatus();

        $alreadyNotified = $campaign->user->notifications()
            ->where('type', PublishCampaignFinishedNotification::class)
            ->where('data->publish_campaign_id', $campaign->id)
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        $campaign->user->notify(new PublishCampaignFinishedNotification($campaign));
    }
}
