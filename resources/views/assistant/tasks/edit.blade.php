<x-ui.page title="Edit Task" maxWidth="3xl">
    <x-ui.form-card title="Task details">
        @include('assistant.tasks.partials.form', ['action' => route('assistant.tasks.update', $task), 'method' => 'PUT', 'task' => $task, 'priorities' => $priorities, 'statuses' => $statuses])
    </x-ui.form-card>
</x-ui.page>
