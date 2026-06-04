<?php

namespace App\Notifications;

use App\Models\PublishJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PublishJobFinishedNotification extends Notification
{
    use Queueable;

    protected $job;
    protected $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(PublishJob $job, string $status)
    {
        $this->job = $job;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $mediaTitle = $this->job->mediaAsset?->title ?? 'Untitled media';
        $channelName = $this->job->distributionChannel?->name ?? 'Unknown channel';

        if ($this->status === 'success') {
            $msg = "Publication successful: '{$mediaTitle}' has been published to {$channelName}.";
        } else {
            $msg = "Publication failed: '{$mediaTitle}' could not be published to {$channelName}. Check logs.";
        }

        return [
            'publish_job_id' => $this->job->id,
            'media_title' => $mediaTitle,
            'channel_name' => $channelName,
            'status' => $this->status,
            'published_url' => $this->job->published_url,
            'message' => $msg,
            'type' => 'distribution',
        ];
    }
}
