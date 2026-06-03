<x-ui.page title="Create Task" maxWidth="3xl">
    <x-ui.form-card title="Task details">
        @include('assistant.tasks.partials.form', ['action' => route('assistant.tasks.store'), 'method' => 'POST', 'task' => null, 'priorities' => $priorities, 'statuses' => $statuses])
    </x-ui.form-card>
</x-ui.page>
