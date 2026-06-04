<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\MediaAsset;
use App\Models\PublishJob;
use App\Models\Task;
use App\Models\Transaction;
use Illuminate\View\View;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $userId = auth()->id();

        // Check for task deadline warnings
        $this->checkUpcomingTasks();

        $openTasks = Task::query()->where('user_id', $userId)->whereNotIn('status', ['completed', 'cancelled'])->count();
        $income = Transaction::query()->where('user_id', $userId)->where('type', 'income')->sum('amount');
        $expense = Transaction::query()->where('user_id', $userId)->where('type', 'expense')->sum('amount');
        $mediaCount = MediaAsset::query()->where('user_id', $userId)->count();
        $pendingPublishes = PublishJob::query()->where('user_id', $userId)->whereIn('status', ['pending', 'scheduled'])->count();
        $budgetCount = Budget::query()->where('user_id', $userId)->count();

        $recentTasks = Task::query()->where('user_id', $userId)->latest()->limit(5)->get();
        $recentTransactions = Transaction::query()->where('user_id', $userId)->latest()->limit(5)->get();

        // --- APEXCHARTS AGGREGATIONS ---

        // 1. 30-Day Daily Cash Flow
        $days = [];
        $incomeSeries = [];
        $expenseSeries = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $days[] = Carbon::now()->subDays($i)->format('M d');
            $incomeSeries[$date] = 0;
            $expenseSeries[$date] = 0;
        }

        $startDate = Carbon::now()->subDays(29)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $transactions = Transaction::where('user_id', $userId)
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->get();

        foreach ($transactions as $tx) {
            $dateStr = Carbon::parse($tx->occurred_at)->toDateString();
            if (isset($incomeSeries[$dateStr])) {
                if ($tx->type === 'income') {
                    $incomeSeries[$dateStr] += (float) $tx->amount;
                } else {
                    $expenseSeries[$dateStr] += (float) $tx->amount;
                }
            }
        }

        $cashFlowData = [
            'categories' => $days,
            'income' => array_values($incomeSeries),
            'expense' => array_values($expenseSeries),
        ];

        // 2. Budget vs Actual Comparison
        $budgets = Budget::where('user_id', $userId)->get();
        $budgetChartData = [
            'categories' => [],
            'limits' => [],
            'actuals' => [],
        ];
        
        foreach ($budgets as $budget) {
            $spent = Transaction::where('user_id', $userId)
                ->where('category', $budget->category)
                ->where('type', 'expense')
                ->whereMonth('occurred_at', Carbon::now()->month)
                ->whereYear('occurred_at', Carbon::now()->year)
                ->sum('amount');
                
            $budgetChartData['categories'][] = $budget->category;
            $budgetChartData['limits'][] = (float) $budget->amount;
            $budgetChartData['actuals'][] = (float) $spent;
        }

        // 3. Publication Queue Channels Mix
        $publishJobs = PublishJob::where('user_id', $userId)
            ->with('distributionChannel')
            ->get()
            ->groupBy('distributionChannel.name');

        $publicationMixData = [
            'labels' => [],
            'series' => [],
        ];
        
        foreach ($publishJobs as $channelName => $jobs) {
            $publicationMixData['labels'][] = $channelName ?: 'Unknown';
            $publicationMixData['series'][] = $jobs->count();
        }

        return view('dashboard', compact(
            'openTasks',
            'income',
            'expense',
            'mediaCount',
            'pendingPublishes',
            'budgetCount',
            'recentTasks',
            'recentTransactions',
            'cashFlowData',
            'budgetChartData',
            'publicationMixData',
        ));
    }

    private function checkUpcomingTasks(): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $upcomingTasks = Task::where('user_id', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereBetween('due_at', [now(), now()->addHours(24)])
            ->get();

        foreach ($upcomingTasks as $task) {
            $notified = $user->notifications()
                ->where('type', 'App\Notifications\TaskUpcomingNotification')
                ->where('data->task_id', $task->id)
                ->exists();

            if (!$notified) {
                $user->notify(new \App\Notifications\TaskUpcomingNotification($task));
            }
        }
    }
}
