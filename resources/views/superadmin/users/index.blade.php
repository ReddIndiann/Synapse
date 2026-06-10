<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl leading-tight">All Users</h2>
            <div class="flex gap-2">
                <form method="GET" class="flex gap-2">
                    <input type="text" name="search" placeholder="Search users..." value="{{ request('search') }}"
                           class="px-3 py-1.5 rounded-lg border border-border bg-surface text-sm focus:ring-2 focus:ring-purple-500/50 outline-none">
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-500">Search</button>
                </form>
                <a href="{{ route('superadmin.users.create') }}" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-500">New User</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-muted border-b border-border">
                            <th class="text-left py-3 px-2">Name</th>
                            <th class="text-left py-3 px-2">Email</th>
                            <th class="text-left py-3 px-2">Roles</th>
                            <th class="text-center py-3 px-2">Tasks</th>
                            <th class="text-center py-3 px-2">Transactions</th>
                            <th class="text-center py-3 px-2">Joined</th>
                            <th class="text-right py-3 px-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr class="border-b border-border/50 hover:bg-surface/50">
                                <td class="py-3 px-2">{{ $user->name }}</td>
                                <td class="py-3 px-2 text-muted">{{ $user->email }}</td>
                                <td class="py-3 px-2">
                                    @foreach($user->roles as $role)
                                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-purple-500/20 text-purple-300">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td class="py-3 px-2 text-center">{{ $user->tasks_count }}</td>
                                <td class="py-3 px-2 text-center">{{ $user->transactions_count }}</td>
                                <td class="py-3 px-2 text-center text-muted">{{ $user->created_at->format('M j, Y') }}</td>
                                <td class="py-3 px-2 text-right">
                                    <div class="flex gap-2 justify-end">
                                        <a href="{{ route('superadmin.users.show', $user) }}" class="text-purple-400 hover:text-purple-300">View</a>
                                        <a href="{{ route('superadmin.users.edit', $user) }}" class="text-amber-400 hover:text-amber-300">Edit</a>
                                        <form method="POST" action="{{ route('superadmin.users.destroy', $user) }}" class="inline" data-confirm="Delete user {{ $user->name }}?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-rose-400 hover:text-rose-300">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $users->links() }}</div>
        </x-ui.card>
    </div>
</x-app-layout>
