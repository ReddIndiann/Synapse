<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assistant\TaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $query = Task::query()->where('user_id', auth()->id());

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        $tasks = $query->latest()->get();

        return view('assistant.tasks.index', compact('tasks'));
    }

    public function create(): View
    {
        return view('assistant.tasks.create', [
            'priorities' => Task::priorities(),
            'statuses' => Task::statuses(),
        ]);
    }

    public function store(TaskRequest $request): RedirectResponse
    {
        $task = Task::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
            'completed_at' => $request->validated('status') === 'completed' ? now() : null,
        ]);

        return redirect()->route('assistant.tasks.index')->with('status', 'Task created.');
    }

    public function edit(Task $task): View
    {
        $this->authorize('view', $task);

        return view('assistant.tasks.edit', [
            'task' => $task,
            'priorities' => Task::priorities(),
            'statuses' => Task::statuses(),
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        if ($request->wantsJson()) {
            $validated = $request->validate([
                'status' => ['required', 'in:' . implode(',', Task::statuses())],
            ]);

            $task->status = $validated['status'];
            $task->completed_at = $validated['status'] === 'completed' ? ($task->completed_at ?? now()) : null;
            $task->save();

            return response()->json([
                'success' => true,
                'task' => $task,
            ]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:' . implode(',', Task::priorities())],
            'status' => ['required', 'in:' . implode(',', Task::statuses())],
            'due_at' => ['nullable', 'date'],
        ]);

        $task->fill($validated);
        $task->completed_at = $validated['status'] === 'completed' ? ($task->completed_at ?? now()) : null;
        $task->save();

        return redirect()->route('assistant.tasks.index')->with('status', 'Task updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);
        $task->delete();

        return redirect()->route('assistant.tasks.index')->with('status', 'Task deleted.');
    }

    public function upcomingAlerts(Request $request)
    {
        $userId = auth()->id();
        $tasks = Task::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('due_at')
            ->get();

        $alerts = [];

        foreach ($tasks as $task) {
            $dueAt = Carbon::parse($task->due_at);
            $diffInSeconds = $dueAt->timestamp - time();
            $diffInMinutes = (int) ($diffInSeconds / 60);

            $threshold = null;
            if ($diffInMinutes >= 0 && $diffInMinutes <= 5) {
                $threshold = '5m';
            } elseif ($diffInMinutes > 5 && $diffInMinutes <= 30) {
                $threshold = '30m';
            } elseif ($diffInMinutes > 30 && $diffInMinutes <= 60) {
                $threshold = '1h';
            }

            if ($threshold) {
                $timestamp = $dueAt->timestamp;
                $cacheKey = "task_alert_shown_{$task->id}_{$threshold}_{$timestamp}";

                if (!Cache::has($cacheKey)) {
                    $alerts[] = [
                        'id' => $task->id,
                        'title' => $task->title,
                        'due_at' => $task->due_at->toDateTimeString(),
                        'threshold' => $threshold,
                        'minutes_remaining' => $diffInMinutes,
                    ];

                    Cache::put($cacheKey, true, now()->addHours(2));

                    if ($threshold === '5m') {
                        Cache::put("task_alert_shown_{$task->id}_30m_{$timestamp}", true, now()->addHours(2));
                        Cache::put("task_alert_shown_{$task->id}_1h_{$timestamp}", true, now()->addHours(2));
                    } elseif ($threshold === '30m') {
                        Cache::put("task_alert_shown_{$task->id}_1h_{$timestamp}", true, now()->addHours(2));
                    }
                }
            }
        }

        return response()->json($alerts);
    }

    public function autoReschedule(Task $task)
    {
        $this->authorize('update', $task);

        $userId = auth()->id();
        $dueAt = Carbon::parse($task->due_at ?: now());
        
        $altSlot = $dueAt->copy()->addHour()->startOfMinute();
        while (Task::query()
            ->where('user_id', $userId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereBetween('due_at', [$altSlot->copy()->subHour(), $altSlot->copy()->addHour()])
            ->exists()
        ) {
            $altSlot->addHour();
        }

        $task->due_at = $altSlot;
        $task->status = 'pending';
        $task->save();

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => "Task rescheduled to conflict-free slot: " . $altSlot->format('M j, Y \a\t h:i A'),
        ]);
    }

    public function cancelTask(Task $task)
    {
        $this->authorize('update', $task);

        $task->status = 'cancelled';
        $task->save();

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => "Task '{$task->title}' has been cancelled.",
        ]);
    }

    public function rescheduleTo(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'due_at' => ['required', 'date', 'after:now'],
        ]);

        $task->due_at = Carbon::parse($validated['due_at']);
        $task->status = 'pending';
        $task->save();

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => "Task rescheduled to " . $task->due_at->format('M j, Y \a\t h:i A'),
        ]);
    }

}
