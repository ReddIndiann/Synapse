<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\PublishJob;
use App\Models\Transaction;
use App\Services\AccountingLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    protected $ledgerService;

    public function __construct(AccountingLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function index(): View
    {
        $userId = auth()->id();

        // 1. Double Entry Ledger Reports
        $trialBalance = $this->ledgerService->getTrialBalance($userId);
        $profitAndLoss = $this->ledgerService->getProfitAndLoss($userId);
        $balanceSheet = $this->ledgerService->getBalanceSheet($userId);

        // 2. Budget summaries
        $budgets = Budget::query()->where('user_id', $userId)->latest()->limit(5)->get();

        // 3. Fallbacks and aggregations for cards
        $income = $profitAndLoss['total_revenue'];
        $expense = $profitAndLoss['total_expense'];
        $netPosition = $profitAndLoss['net_income'];
        
        $byCategory = Transaction::query()
            ->where('user_id', $userId)
            ->select('category', 'type', DB::raw('SUM(amount * exchange_rate) as total'))
            ->groupBy('category', 'type')
            ->orderByDesc('total')
            ->get();

        $distributionEconomics = PublishJob::query()
            ->where('user_id', $userId)
            ->where('status', 'published')
            ->with('distributionChannel')
            ->get()
            ->groupBy('distribution_channel_id')
            ->map(function ($jobs) use ($userId) {
                $channelName = $jobs->first()->distributionChannel?->name ?? 'Unknown';
                $channelId = $jobs->first()->distribution_channel_id;
                $linkedSpend = Transaction::query()
                    ->where('user_id', $userId)
                    ->where('type', 'expense')
                    ->where('category', 'Marketing')
                    ->whereNotNull('publish_job_id')
                    ->whereHas('publishJob', fn ($q) => $q->where('distribution_channel_id', $channelId))
                    ->sum('amount');

                return [
                    'channel' => $channelName,
                    'jobs_published' => $jobs->count(),
                    'linked_spend' => (float) $linkedSpend,
                ];
            })
            ->values();

        return view('accounting.reports.index', compact(
            'income',
            'expense',
            'netPosition',
            'byCategory',
            'budgets',
            'trialBalance',
            'profitAndLoss',
            'balanceSheet',
            'distributionEconomics',
        ));
    }
}
