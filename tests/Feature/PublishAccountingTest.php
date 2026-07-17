<?php

namespace Tests\Feature;

use App\Events\PublishJobPublished;
use App\Models\Budget;
use App\Models\DistributionChannel;
use App\Models\MediaAsset;
use App\Models\PublishCampaign;
use App\Models\PublishJob;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PublishAccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PublishAccountingTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_expense_is_recorded_when_campaign_records_cost(): void
    {
        Event::fake([PublishJobPublished::class]);

        $user = User::factory()->create();
        $asset = MediaAsset::factory()->create(['user_id' => $user->id]);
        $channel = DistributionChannel::factory()->create();

        $campaign = PublishCampaign::factory()->create([
            'user_id' => $user->id,
            'media_asset_id' => $asset->id,
            'record_cost' => true,
            'estimated_cost_per_channel' => 50,
            'currency' => 'GHS',
        ]);

        $job = PublishJob::factory()->create([
            'user_id' => $user->id,
            'publish_campaign_id' => $campaign->id,
            'media_asset_id' => $asset->id,
            'distribution_channel_id' => $channel->id,
            'status' => 'published',
        ]);

        $transaction = app(PublishAccountingService::class)->recordPublishExpense($job);

        $this->assertNotNull($transaction);
        $this->assertDatabaseHas('transactions', [
            'publish_job_id' => $job->id,
            'category' => 'Marketing',
            'type' => 'expense',
            'amount' => 50,
        ]);
    }

    public function test_budget_service_respects_quarterly_period(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'category' => 'Marketing',
            'amount' => 1000,
            'period' => 'quarterly',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category' => 'Marketing',
            'type' => 'expense',
            'amount' => 200,
            'occurred_at' => now()->startOfQuarter()->addDay(),
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category' => 'Marketing',
            'type' => 'expense',
            'amount' => 999,
            'occurred_at' => now()->subQuarter(),
        ]);

        $spent = app(\App\Services\BudgetService::class)->spentForBudget($budget);

        $this->assertEquals(200.0, $spent);
    }

    public function test_batch_notification_sent_when_campaign_completes(): void
    {
        $user = User::factory()->create();
        $campaign = PublishCampaign::factory()->create(['user_id' => $user->id, 'status' => 'queued']);

        $job = PublishJob::factory()->create([
            'user_id' => $user->id,
            'publish_campaign_id' => $campaign->id,
            'status' => 'published',
        ]);

        event(new PublishJobPublished($job));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => \App\Notifications\PublishCampaignFinishedNotification::class,
        ]);
    }
}
