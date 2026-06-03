<x-ui.page :title="__('Edit Role')" maxWidth="3xl">
    <x-ui.form-card title="Role details">
        @include('admin.roles.partials.form', [
            'action' => route('admin.roles.update', $role),
            'method' => 'PUT',
            'role' => $role,
        ])
    </x-ui.form-card>
</x-ui.page>
