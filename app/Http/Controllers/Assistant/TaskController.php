<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $tasks = Task::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('assistant.tasks.index', compact('tasks'));
    }

    public function create(): View
    {
        return view('assistant.tasks.create', [
            'priorities' => Task::priorities(),
            'statuses' => Task::statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:'.implode(',', Task::priorities())],
            'status' => ['required', 'in:'.implode(',', Task::statuses())],
            'due_at' => ['nullable', 'date'],
        ]);

        Task::create([
            ...$validated,
            'user_id' => auth()->id(),
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ]);

        return redirect()->route('assistant.tasks.index')->with('status', 'Task created.');
    }

    public function edit(Task $task): View
    {
        $this->authorizeTask($task);

        return view('assistant.tasks.edit', [
            'task' => $task,
            'priorities' => Task::priorities(),
            'statuses' => Task::statuses(),
        ]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($task);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:'.implode(',', Task::priorities())],
            'status' => ['required', 'in:'.implode(',', Task::statuses())],
            'due_at' => ['nullable', 'date'],
        ]);

        $task->fill($validated);
        $task->completed_at = $validated['status'] === 'completed' ? ($task->completed_at ?? now()) : null;
        $task->save();

        return redirect()->route('assistant.tasks.index')->with('status', 'Task updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorizeTask($task);
        $task->delete();

        return redirect()->route('assistant.tasks.index')->with('status', 'Task deleted.');
    }

    private function authorizeTask(Task $task): void
    {
        abort_unless($task->user_id === auth()->id(), 403);
    }
}
