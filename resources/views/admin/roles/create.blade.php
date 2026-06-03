<x-ui.page :title="__('Create Role')" maxWidth="3xl">
    <x-ui.form-card title="Role details">
        @include('admin.roles.partials.form', [
            'action' => route('admin.roles.store'),
            'method' => 'POST',
            'role' => null,
        ])
    </x-ui.form-card>
</x-ui.page>
