<?php

namespace Tests\Feature;

use App\Models\DistributionChannel;
use App\Models\MediaAsset;
use App\Models\PublishCampaign;
use App\Models\PublishJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DistributionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Storage::fake('public');
    }

    public function test_media_index_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('distribution.media.index'));

        $response->assertOk();
    }

    public function test_media_can_be_uploaded(): void
    {
        $file = UploadedFile::fake()->image('test-image.jpg');

        $response = $this->actingAs($this->user)->post(route('distribution.media.store'), [
            'title' => 'Test Image',
            'file' => $file,
            'status' => 'ready',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('distribution.media.index'));

        $this->assertDatabaseHas('media_assets', [
            'user_id' => $this->user->id,
            'title' => 'Test Image',
            'filename' => 'test-image.jpg',
        ]);
    }

    public function test_media_rejects_invalid_file_type(): void
    {
        $file = UploadedFile::fake()->create('document.exe', 100);

        $response = $this->actingAs($this->user)->post(route('distribution.media.store'), [
            'title' => 'Bad File',
            'file' => $file,
            'status' => 'ready',
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_media_rejects_oversized_file(): void
    {
        $file = UploadedFile::fake()->create('large.pdf', 10241);

        $response = $this->actingAs($this->user)->post(route('distribution.media.store'), [
            'title' => 'Large File',
            'file' => $file,
            'status' => 'ready',
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_media_can_be_updated(): void
    {
        $asset = MediaAsset::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->put(route('distribution.media.update', $asset), [
            'title' => 'Updated Title',
            'status' => 'ready',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('media_assets', [
            'id' => $asset->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_media_can_be_deleted(): void
    {
        $asset = MediaAsset::factory()->create([
            'user_id' => $this->user->id,
            'path' => 'media/test.jpg',
        ]);

        $response = $this->actingAs($this->user)->delete(route('distribution.media.destroy', $asset));

        $response->assertRedirect(route('distribution.media.index'));
        $this->assertDatabaseMissing('media_assets', ['id' => $asset->id]);
    }

    public function test_multi_platform_campaign_can_be_created(): void
    {
        Queue::fake();

        $asset = MediaAsset::factory()->create(['user_id' => $this->user->id]);
        $website = DistributionChannel::factory()->create(['slug' => 'website', 'is_active' => true]);
        $audiomack = DistributionChannel::factory()->create(['slug' => 'audiomack', 'is_active' => true]);

        $response = $this->actingAs($this->user)->post(route('distribution.publish.store'), [
            'media_asset_id' => $asset->id,
            'distribution_channel_ids' => [$website->id, $audiomack->id],
            'caption' => 'Test publish',
        ]);

        $response->assertSessionHasNoErrors();

        $campaign = PublishCampaign::first();
        $this->assertNotNull($campaign);
        $this->assertEquals(2, $campaign->publishJobs()->count());

        $this->assertDatabaseHas('publish_jobs', [
            'user_id' => $this->user->id,
            'publish_campaign_id' => $campaign->id,
            'distribution_channel_id' => $website->id,
        ]);
    }

    public function test_publish_campaign_with_schedule(): void
    {
        $asset = MediaAsset::factory()->create(['user_id' => $this->user->id]);
        $channel = DistributionChannel::factory()->create(['slug' => 'website', 'is_active' => true]);

        $response = $this->actingAs($this->user)->post(route('distribution.publish.store'), [
            'media_asset_id' => $asset->id,
            'distribution_channel_ids' => [$channel->id],
            'scheduled_at' => now()->addDays(2)->toDateTimeString(),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('publish_jobs', [
            'media_asset_id' => $asset->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_scheduled_dispatcher_dispatches_due_jobs(): void
    {
        Queue::fake();

        $job = PublishJob::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'scheduled',
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->artisan('publish:dispatch-scheduled')->assertSuccessful();

        $job->refresh();
        $this->assertEquals('pending', $job->status);
        Queue::assertPushed(\App\Jobs\ProcessPublishJob::class);
    }

    public function test_publish_job_status_json(): void
    {
        $job = PublishJob::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('distribution.publish.status', $job));

        $response->assertOk();
        $response->assertJsonStructure(['status', 'logs', 'published_url', 'progress']);
    }

    public function test_campaign_monitor_status_json(): void
    {
        $campaign = PublishCampaign::factory()->create(['user_id' => $this->user->id]);
        PublishJob::factory()->create([
            'user_id' => $this->user->id,
            'publish_campaign_id' => $campaign->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('distribution.publish.campaign.status', $campaign));

        $response->assertOk();
        $response->assertJsonStructure(['campaign_status', 'progress_summary', 'jobs']);
    }

    public function test_user_cannot_access_other_users_publish_job(): void
    {
        $otherUser = User::factory()->create();
        $job = PublishJob::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->get(route('distribution.publish.monitor', $job));

        $response->assertForbidden();
    }
}
