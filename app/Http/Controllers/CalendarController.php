<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Transaction;
use App\Models\PublishJob;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $month = (int) $request->get('month', date('n'));
        $year = (int) $request->get('year', date('Y'));

        // Handle bounds wrap-around for nav
        if ($month < 1) {
            $month = 12;
            $year--;
        } elseif ($month > 12) {
            $month = 1;
            $year++;
        }

        $currentDate = Carbon::create($year, $month, 1);
        
        $startOfCalendar = $currentDate->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar = $currentDate->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        // Fetch user data
        $userId = auth()->id();
        
        $tasks = Task::where('user_id', $userId)
            ->whereBetween('due_at', [$startOfCalendar, $endOfCalendar->endOfDay()])
            ->get();

        $transactions = Transaction::where('user_id', $userId)
            ->whereBetween('occurred_at', [$startOfCalendar, $endOfCalendar])
            ->get();

        $publishJobs = PublishJob::where('user_id', $userId)
            ->whereBetween('scheduled_at', [$startOfCalendar, $endOfCalendar->endOfDay()])
            ->with(['mediaAsset', 'distributionChannel'])
            ->get();

        // Build grid of days
        $date = $startOfCalendar->copy();
        $days = [];
        
        while ($date->lte($endOfCalendar)) {
            $dayDateStr = $date->toDateString();
            $events = [];

            // Match Tasks
            foreach ($tasks as $task) {
                if ($task->due_at && $task->due_at->toDateString() === $dayDateStr) {
                    $events[] = [
                        'id' => 'task-' . $task->id,
                        'type' => 'task',
                        'title' => $task->title,
                        'time' => $task->due_at->format('g:i A'),
                        'detail' => 'Priority: ' . ucfirst($task->priority) . ' · Status: ' . str_replace('_', ' ', $task->status),
                        'url' => route('assistant.tasks.edit', $task),
                        'color' => 'violet',
                    ];
                }
            }

            // Match Transactions
            foreach ($transactions as $tx) {
                if ($tx->occurred_at && $tx->occurred_at->toDateString() === $dayDateStr) {
                    $events[] = [
                        'id' => 'tx-' . $tx->id,
                        'type' => 'transaction',
                        'title' => ($tx->type === 'income' ? '+ ' : '- ') . number_format($tx->amount, 2) . ' ' . $tx->currency,
                        'time' => 'Financial',
                        'detail' => ucfirst($tx->type) . ' · Category: ' . $tx->category,
                        'url' => route('accounting.transactions.edit', $tx),
                        'color' => 'emerald',
                    ];
                }
            }

            // Match Publish Jobs
            foreach ($publishJobs as $job) {
                if ($job->scheduled_at && $job->scheduled_at->toDateString() === $dayDateStr) {
                    $events[] = [
                        'id' => 'job-' . $job->id,
                        'type' => 'publish_job',
                        'title' => 'Publish to ' . ($job->distributionChannel?->name ?? 'Channel'),
                        'time' => $job->scheduled_at->format('g:i A'),
                        'detail' => 'Asset: ' . ($job->mediaAsset?->title ?? 'Media') . ' · Status: ' . ucfirst($job->status),
                        'url' => $job->status === 'pending' || $job->status === 'processing'
                            ? route('distribution.publish.monitor', $job)
                            : route('distribution.publish.index'),
                        'color' => 'sky',
                    ];
                }
            }

            $days[] = [
                'date' => $date->copy(),
                'day_number' => $date->day,
                'is_current_month' => $date->month === $month,
                'is_today' => $date->isToday(),
                'events' => $events,
            ];

            $date->addDay();
        }

        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        return view('calendar.index', [
            'days' => $days,
            'currentMonthName' => $currentDate->format('F Y'),
            'month' => $month,
            'year' => $year,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
        ]);
    }
}
