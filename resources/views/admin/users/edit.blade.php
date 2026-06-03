<x-ui.page :title="__('Edit User')" maxWidth="3xl">
    <x-ui.form-card title="User details" description="Update profile and role assignments.">
        @include('admin.users.partials.form', [
            'action' => route('admin.users.update', $user),
            'method' => 'PUT',
            'user' => $user,
            'roles' => $roles,
            'selectedRoles' => $user->roles->pluck('name')->all(),
        ])
    </x-ui.form-card>
</x-ui.page>
