<x-ui.page :title="__('Edit Role')" maxWidth="3xl">
    <x-slot name="actions">
        <x-ui.button :href="route('admin.roles.index')" variant="secondary" size="sm">&larr; Back to Roles</x-ui.button>
    </x-slot>
    <x-ui.form-card title="Role details">
        @include('admin.roles.partials.form', [
            'action' => route('admin.roles.update', $role),
            'method' => 'PUT',
            'role' => $role,
        ])
    </x-ui.form-card>
</x-ui.page>
