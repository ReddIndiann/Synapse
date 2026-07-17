<?php

namespace App\Notifications;

use App\Models\PublishCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PublishCampaignFinishedNotification extends Notification
{
    use Queueable;

    public function __construct(public PublishCampaign $campaign) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $jobs = $this->campaign->publishJobs()->with('distributionChannel')->get();
        $published = $jobs->where('status', 'published')->count();
        $failed = $jobs->where('status', 'failed')->count();
        $total = $jobs->count();

        $mediaTitle = $this->campaign->mediaAsset?->title ?? 'Untitled media';

        return [
            'publish_campaign_id' => $this->campaign->id,
            'media_title' => $mediaTitle,
            'published_count' => $published,
            'failed_count' => $failed,
            'total_count' => $total,
            'status' => $this->campaign->status,
            'message' => "Campaign complete: {$published}/{$total} channels published for '{$mediaTitle}'.",
            'type' => 'distribution',
        ];
    }
}
