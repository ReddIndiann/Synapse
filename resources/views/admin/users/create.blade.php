<x-ui.page :title="__('Create User')" maxWidth="3xl">
    <x-ui.form-card title="User details" description="Add a new person to the platform.">
        @include('admin.users.partials.form', [
            'action' => route('admin.users.store'),
            'method' => 'POST',
            'user' => null,
            'roles' => $roles,
            'selectedRoles' => [],
        ])
    </x-ui.form-card>
</x-ui.page>
