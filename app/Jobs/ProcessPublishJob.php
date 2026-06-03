<?php

namespace App\Jobs;

use App\Models\PublishJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ProcessPublishJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $publishJob;

    /**
     * Create a new job instance.
     */
    public function __construct(PublishJob $publishJob)
    {
        $this->publishJob = $publishJob;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = $this->publishJob;
        $job->refresh();

        // 1. Initial log entry
        $logs = [];
        $logs[] = $this->logEntry("Initializing publishing queue connector...");
        $job->update([
            'status' => 'processing',
            'logs' => $logs,
        ]);
        sleep(1);

        // 2. Metadata Extraction
        $media = $job->mediaAsset;
        $channel = $job->distributionChannel;

        $logs[] = $this->logEntry("Extracting media asset metadata...");
        $job->update(['logs' => $logs]);
        sleep(1);

        $sizeMb = number_format($media->size / (1024 * 1024), 2);
        $extractedMeta = [
            'filename' => $media->filename,
            'mime_type' => $media->mime_type,
            'size' => "{$sizeMb} MB",
            'resolution' => Str::contains($media->mime_type, 'video') ? '1920x1080 (1080p)' : 'N/A (Audio/Image)',
            'duration' => Str::contains($media->mime_type, ['video', 'audio']) ? '03:45' : 'N/A',
        ];

        $logs[] = $this->logEntry("Metadata extracted successfully: Filename={$extractedMeta['filename']}, Mime={$extractedMeta['mime_type']}, Size={$extractedMeta['size']}, Resolution={$extractedMeta['resolution']}, Duration={$extractedMeta['duration']}");
        $job->update(['logs' => $logs]);
        sleep(1);

        // 3. Chunked Upload Simulation
        $logs[] = $this->logEntry("Establishing secure handshake with API endpoints for {$channel->name}...");
        $job->update(['logs' => $logs]);
        sleep(1);

        for ($percent = 25; $percent <= 100; $percent += 25) {
            $logs[] = $this->logEntry("Uploading file buffer chunks... {$percent}% complete");
            $job->update(['logs' => $logs]);
            sleep(1);
        }

        // 4. Platform Verification
        $logs[] = $this->logEntry("Verifying payload checksum on platform servers...");
        $job->update(['logs' => $logs]);
        sleep(1);

        $logs[] = $this->logEntry("Platform post-processing and transcoding in progress...");
        $job->update(['logs' => $logs]);
        sleep(1);

        // 5. Finalizing Publish
        $randomHash = Str::random(11);
        $publishedUrl = '';
        if (strtolower($channel->slug) === 'youtube') {
            $publishedUrl = "https://www.youtube.com/watch?v={$randomHash}";
        } elseif (strtolower($channel->slug) === 'spotify') {
            $publishedUrl = "https://open.spotify.com/track/" . Str::random(22);
        } elseif (strtolower($channel->slug) === 'audiomack') {
            $publishedUrl = "https://audiomack.com/song/synapse/" . Str::slug($media->title);
        } else {
            $publishedUrl = "https://{$channel->slug}.com/synapse/" . Str::random(8);
        }

        $logs[] = $this->logEntry("Published successfully! Output live resource URL: {$publishedUrl}");
        
        $job->update([
            'status' => 'published',
            'published_at' => Carbon::now(),
            'published_url' => $publishedUrl,
            'logs' => $logs,
        ]);
    }

    /**
     * Create log entry structure.
     */
    private function logEntry(string $message): array
    {
        return [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'formatted_time' => Carbon::now()->format('H:i:s'),
            'message' => $message,
        ];
    }
}
