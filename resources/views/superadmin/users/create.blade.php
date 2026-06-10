<x-ui.page title="Create User" maxWidth="3xl">
    <x-slot name="actions">
        <x-ui.button :href="route('superadmin.users.index')" variant="secondary" size="sm">&larr; Back to Users</x-ui.button>
    </x-slot>
    <x-ui.form-card title="User details" description="Create a new user account.">
        <form method="POST" action="{{ route('superadmin.users.store') }}" class="space-y-4">
            @csrf
            <div>
                <x-ui.input label="Name" name="name" :value="old('name')" required />
            </div>
            <div>
                <x-ui.input label="Email" name="email" type="email" :value="old('email')" required />
            </div>
            <div>
                <x-ui.input label="Password" name="password" type="password" required />
            </div>
            <div>
                <x-ui.input label="Confirm Password" name="password_confirmation" type="password" required />
            </div>
            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-2">Roles</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($roles as $role)
                        <label class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-[var(--border)] bg-[var(--surface)] text-xs cursor-pointer hover:border-[var(--pur)]/40">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}
                                   class="rounded border-[var(--border)] text-[var(--pur)] focus:ring-[var(--pur)]/50">
                            {{ $role->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit" variant="primary">Create User</x-ui.button>
                <x-ui.button :href="route('superadmin.users.index')" variant="secondary">Cancel</x-ui.button>
            </div>
        </form>
    </x-ui.form-card>
</x-ui.page>