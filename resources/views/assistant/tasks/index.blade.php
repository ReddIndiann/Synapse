<x-ui.page title="Tasks" description="Manage and schedule your work.">
    <x-slot name="actions">
        <x-ui.button :href="route('assistant.tasks.create')" variant="primary" size="sm">New Task</x-ui.button>
    </x-slot>

    <x-ui.table-shell>
        <thead>
            <tr>
                <th class="py-2 font-medium">Title</th>
                <th class="py-2 font-medium">Priority</th>
                <th class="py-2 font-medium">Status</th>
                <th class="py-2 font-medium">Due</th>
                <th class="py-2 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tasks as $task)
                <tr>
                    <td class="py-3">
                        <p class="font-medium text-slate-800">{{ $task->title }}</p>
                        @if ($task->description)
                            <p class="text-xs text-slate-500 mt-0.5">{{ Str::limit($task->description, 60) }}</p>
                        @endif
                    </td>
                    <td class="py-3"><x-ui.badge variant="{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'default') }}">{{ $task->priority }}</x-ui.badge></td>
                    <td class="py-3"><x-ui.badge variant="{{ $task->status === 'completed' ? 'success' : 'primary' }}">{{ $task->status }}</x-ui.badge></td>
                    <td class="py-3 text-sm">{{ $task->due_at?->format('M j, Y') ?? '—' }}</td>
                    <td class="py-2">
                        <div class="flex gap-3">
                            <x-ui.button :href="route('assistant.tasks.edit', $task)" variant="link" size="sm">Edit</x-ui.button>
                            <form method="POST" action="{{ route('assistant.tasks.destroy', $task) }}">
                                @csrf @method('DELETE')
                                <x-ui.button type="submit" variant="link" size="sm" class="!text-red-600" onclick="return confirm('Delete task?')">Delete</x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-ui.empty-state title="No tasks yet"><x-slot name="action"><x-ui.button :href="route('assistant.tasks.create')" variant="primary" size="sm">Create task</x-ui.button></x-slot></x-ui.empty-state></td></tr>
            @endforelse
        </tbody>
        <x-slot name="footer">{{ $tasks->links() }}</x-slot>
    </x-ui.table-shell>
</x-ui.page>
