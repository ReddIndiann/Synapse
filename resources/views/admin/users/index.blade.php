<x-ui.page title="Users" description="Manage platform users and role assignments.">
    <x-slot name="actions">
        <x-ui.button :href="route('admin.users.create')" variant="primary" size="sm">New User</x-ui.button>
    </x-slot>

    <x-ui.table-shell>
        <thead>
            <tr>
                <th class="py-2 font-medium">Name</th>
                <th class="py-2 font-medium">Email</th>
                <th class="py-2 font-medium">Phone</th>
                <th class="py-2 font-medium">Roles</th>
                <th class="py-2 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td class="py-3">{{ $user->name }}</td>
                    <td class="py-3">{{ $user->email }}</td>
                    <td class="py-3">{{ $user->phone ?? '-' }}</td>
                    <td class="py-3">
                        @forelse ($user->roles as $role)
                            <x-ui.badge variant="primary" class="mr-1">{{ $role->name }}</x-ui.badge>
                        @empty
                            <span class="text-slate-400">-</span>
                        @endforelse
                    </td>
                    <td class="py-2">
                        <div class="flex items-center gap-3">
                            <x-ui.button :href="route('admin.users.edit', $user)" variant="link" size="sm">Edit</x-ui.button>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="link" size="sm" class="!text-red-600 hover:!text-red-800" onclick="return confirm('Delete this user?')">Delete</x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-ui.empty-state title="No users found" description="Create your first user to get started.">
                            <x-slot name="action">
                                <x-ui.button :href="route('admin.users.create')" variant="primary" size="sm">New User</x-ui.button>
                            </x-slot>
                        </x-ui.empty-state>
                    </td>
                </tr>
            @endforelse
        </tbody>

        <x-slot name="footer">{{ $users->links() }}</x-slot>
    </x-ui.table-shell>
</x-ui.page>
