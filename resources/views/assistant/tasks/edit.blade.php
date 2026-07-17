<x-ui.page title="Edit Task" maxWidth="3xl">
    <x-ui.back-link :href="route('assistant.tasks.index')" label="Back to Tasks" />
    <x-ui.form-card title="Task details">
        @include('assistant.tasks.partials.form', ['action' => route('assistant.tasks.update', $task), 'method' => 'PUT', 'task' => $task, 'priorities' => $priorities, 'statuses' => $statuses])
    </x-ui.form-card>

    <div class="mt-6 rounded-2xl border border-rose-500/20 bg-rose-500/5 p-5">
        <h3 class="text-sm font-bold text-rose-600 dark:text-rose-400 mb-1">Delete task</h3>
        <p class="text-xs text-[var(--text-secondary)] mb-4">Permanently remove "{{ $task->title }}" from your backlog. This cannot be undone.</p>
        <form method="POST" action="{{ route('assistant.tasks.destroy', $task) }}" data-confirm="Delete task '{{ $task->title }}'?">
            @csrf
            @method('DELETE')
            <x-ui.button type="submit" variant="danger" size="sm">Delete Task</x-ui.button>
        </form>
    </div>
</x-ui.page>
