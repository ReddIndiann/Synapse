<x-ui.page title="Roles" description="Create and manage access roles.">
    <x-slot name="actions">
        <x-ui.button :href="route('admin.roles.create')" variant="primary" size="sm">New Role</x-ui.button>
    </x-slot>

    <x-ui.table-shell>
        <thead>
            <tr>
                <th class="py-2 font-medium">Role</th>
                <th class="py-2 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($roles as $role)
                <tr>
                    <td class="py-3">
                        <x-ui.badge variant="primary">{{ $role->name }}</x-ui.badge>
                    </td>
                    <td class="py-2">
                        <div class="flex items-center gap-3">
                            <x-ui.button :href="route('admin.roles.edit', $role)" variant="link" size="sm">Edit</x-ui.button>
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="link" size="sm" class="!text-red-600 hover:!text-red-800" onclick="return confirm('Delete this role?')">Delete</x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">
                        <x-ui.empty-state title="No roles found" description="Create a role to assign permissions.">
                            <x-slot name="action">
                                <x-ui.button :href="route('admin.roles.create')" variant="primary" size="sm">New Role</x-ui.button>
                            </x-slot>
                        </x-ui.empty-state>
                    </td>
                </tr>
            @endforelse
        </tbody>

        <x-slot name="footer">{{ $roles->links() }}</x-slot>
    </x-ui.table-shell>
</x-ui.page>
