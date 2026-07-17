<?php

namespace App\Services;

use App\Models\PublishJob;
use App\Models\Transaction;
use App\Notifications\BudgetBreachedNotification;
use Illuminate\Support\Carbon;

class PublishAccountingService
{
    public function __construct(
        private AccountingLedgerService $ledgerService,
        private BudgetService $budgetService,
    ) {}

    public function recordPublishExpense(PublishJob $job): ?Transaction
    {
        $campaign = $job->publishCampaign;
        if (!$campaign || !$campaign->record_cost) {
            return null;
        }

        $amount = (float) ($campaign->estimated_cost_per_channel ?? 0);
        if ($amount <= 0) {
            return null;
        }

        $existing = Transaction::query()
            ->where('publish_job_id', $job->id)
            ->where('type', 'expense')
            ->first();

        if ($existing) {
            return $existing;
        }

        $channelName = $job->distributionChannel?->name ?? 'Unknown';
        $mediaTitle = $job->mediaAsset?->title ?? 'Media';

        $transaction = Transaction::create([
            'user_id' => $job->user_id,
            'publish_job_id' => $job->id,
            'publish_campaign_id' => $campaign->id,
            'media_asset_id' => $job->media_asset_id,
            'type' => 'expense',
            'amount' => $amount,
            'currency' => $campaign->currency ?? 'GHS',
            'category' => 'Marketing',
            'description' => "Distribution cost: {$mediaTitle} → {$channelName}",
            'occurred_at' => Carbon::now(),
            'reference' => "publish_job_{$job->id}",
            'payment_method' => 'Cash',
        ]);

        $this->ledgerService->recordTransaction($transaction);
        $this->checkMarketingBudget($transaction);

        return $transaction;
    }

    private function checkMarketingBudget(Transaction $transaction): void
    {
        if ($transaction->category !== 'Marketing' || $transaction->type !== 'expense') {
            return;
        }

        $budget = \App\Models\Budget::query()
            ->where('user_id', $transaction->user_id)
            ->where('category', 'Marketing')
            ->first();

        if (!$budget || $budget->amount <= 0) {
            return;
        }

        $totalSpent = $this->budgetService->spentForBudget($budget, Carbon::parse($transaction->occurred_at));
        $level = $this->budgetService->breachLevel($budget, $totalSpent);

        if (!$level) {
            return;
        }

        $notified = $transaction->user->notifications()
            ->where('type', BudgetBreachedNotification::class)
            ->where('data->budget_id', $budget->id)
            ->where('data->level', $level)
            ->whereMonth('created_at', Carbon::parse($transaction->occurred_at)->month)
            ->whereYear('created_at', Carbon::parse($transaction->occurred_at)->year)
            ->exists();

        if (!$notified) {
            $transaction->user->notify(new BudgetBreachedNotification($budget, $totalSpent, $level));
        }
    }
}
