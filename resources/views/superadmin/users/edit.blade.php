<x-ui.page title="Edit User" maxWidth="3xl">
    <x-slot name="actions">
        <x-ui.button :href="route('superadmin.users.show', $user)" variant="secondary" size="sm">&larr; Back to User</x-ui.button>
    </x-slot>
    <x-ui.form-card title="User details" description="Update profile and role assignments.">
        <form method="POST" action="{{ route('superadmin.users.update', $user) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <x-ui.input label="Name" name="name" :value="old('name', $user->name)" required />
            </div>
            <div>
                <x-ui.input label="Email" name="email" type="email" :value="old('email', $user->email)" required />
            </div>
            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-2">Roles</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($roles as $role)
                        <label class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-[var(--border)] bg-[var(--surface)] text-xs cursor-pointer hover:border-[var(--pur)]/40">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                   {{ $user->hasRole($role->name) ? 'checked' : '' }}
                                   class="rounded border-[var(--border)] text-[var(--pur)] focus:ring-[var(--pur)]/50">
                            {{ $role->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit" variant="primary">Update User</x-ui.button>
                <x-ui.button :href="route('superadmin.users.show', $user)" variant="secondary">Cancel</x-ui.button>
            </div>
        </form>
    </x-ui.form-card>
</x-ui.page>