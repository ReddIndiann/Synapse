<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <div>
        <x-input-label for="title" value="Title" />
        <x-text-input id="title" class="mt-1" name="title" :value="old('title', $task?->title)" required />
        <x-input-error :messages="$errors->get('title')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="auth-input mt-1 resize-none">{{ old('description', $task?->description) }}</textarea>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="priority" value="Priority" />
            <select id="priority" name="priority" class="auth-input mt-1">
                @foreach ($priorities as $p)
                    <option value="{{ $p }}" @selected(old('priority', $task?->priority) === $p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="auth-input mt-1">
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" @selected(old('status', $task?->status ?? 'pending') === $s)>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <x-input-label for="due_at" value="Due date" />
        <x-text-input id="due_at" class="mt-1" type="datetime-local" name="due_at" :value="old('due_at', $task?->due_at?->format('Y-m-d\TH:i'))" />
    </div>

    <div class="flex gap-3">
        <x-primary-button>{{ $task ? 'Update Task' : 'Create Task' }}</x-primary-button>
        <x-ui.button :href="route('assistant.tasks.index')" variant="ghost" size="sm">Cancel</x-ui.button>
    </div>
</form>
