<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\MediaAsset;
use App\Models\PublishJob;
use App\Models\Task;
use App\Models\Transaction;
use Illuminate\View\View;

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

        return view('dashboard', compact(
            'openTasks',
            'income',
            'expense',
            'mediaCount',
            'pendingPublishes',
            'budgetCount',
            'recentTasks',
            'recentTransactions',
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
