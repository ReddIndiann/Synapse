<x-ui.page title="Tasks" description="Manage and schedule your assistant work.">
    <x-slot name="actions">
        <x-ui.button :href="route('assistant.tasks.create')" variant="primary" size="sm">New Task</x-ui.button>
    </x-slot>

    <x-ui.table-shell>
        <thead>
            <tr>
                <th class="py-3 font-semibold text-slate-500 text-left">Task Description</th>
                <th class="py-3 font-semibold text-slate-500 text-center">Priority</th>
                <th class="py-3 font-semibold text-slate-500 text-center">Status</th>
                <th class="py-3 font-semibold text-slate-500 text-left">Due Date</th>
                <th class="py-3 font-semibold text-slate-500 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tasks as $task)
                <tr class="border-b border-slate-100 hover:bg-slate-50/40 transition-colors">
                    <td class="py-4">
                        <div class="flex items-start gap-3">
                            <div class="w-4.5 h-4.5 mt-0.5 rounded-md border border-[var(--border)] flex items-center justify-center shrink-0 text-white bg-[var(--surface)]/40
                                @if($task->status === 'completed') !bg-[var(--pur)] !border-[var(--pur)] @endif">
                                @if($task->status === 'completed')
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-[var(--text)] text-xs @if($task->status === 'completed') line-through text-[var(--text-secondary)] opacity-60 @endif">{{ $task->title }}</p>
                                @if ($task->description)
                                    <p class="text-[10px] text-[var(--text-secondary)] font-semibold mt-0.5 truncate max-w-[200px] sm:max-w-md">{{ $task->description }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-4 text-center">
                        <x-ui.badge variant="{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'default') }}" class="!py-0.5 !px-2.5 !text-[10px] font-bold">
                            {{ $task->priority }}
                        </x-ui.badge>
                    </td>
                    <td class="py-4 text-center">
                        <x-ui.badge variant="{{ $task->status === 'completed' ? 'success' : 'primary' }}" class="!py-0.5 !px-2.5 !text-[10px] font-bold">
                            {{ str_replace('_', ' ', $task->status) }}
                        </x-ui.badge>
                    </td>
                    <td class="py-4 text-[var(--text-muted)] font-medium text-xs">
                        {{ $task->due_at?->format('M j, Y \a\t g:i A') ?? 'No deadline' }}
                    </td>
                    <td class="py-4">
                        <div class="flex gap-2 justify-center items-center">
                            <x-ui.button :href="route('assistant.tasks.edit', $task)" variant="link" size="sm" class="!text-indigo-600 dark:!text-indigo-400">Edit</x-ui.button>
                            <span class="text-[var(--border)]">|</span>
                            <form method="POST" action="{{ route('assistant.tasks.destroy', $task) }}" class="inline">
                                @csrf @method('DELETE')
                                <x-ui.button type="submit" variant="link" size="sm" class="!text-rose-600 dark:!text-rose-400" onclick="return confirm('Delete task?')">Delete</x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-ui.empty-state title="No tasks scheduled yet">
                            <x-slot name="action">
                                <x-ui.button :href="route('assistant.tasks.create')" variant="primary" size="sm">Create task</x-ui.button>
                            </x-slot>
                        </x-ui.empty-state>
                    </td>
                </tr>
            @endforelse
        </tbody>
        <x-slot name="footer">{{ $tasks->links() }}</x-slot>
    </x-ui.table-shell>
</x-ui.page>
