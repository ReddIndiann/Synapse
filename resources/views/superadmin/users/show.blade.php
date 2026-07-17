<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <x-ui.back-link :href="route('superadmin.users.index')" label="Back to Users" class="!mb-0" />
            <h2 class="font-semibold text-xl leading-tight">{{ $user->name }}</h2>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-ui.stat-card label="Tasks" :value="$stats['tasks']" color="indigo" />
            <x-ui.stat-card label="Pending" :value="$stats['pending_tasks']" color="amber" />
            <x-ui.stat-card label="Income" :value="number_format($stats['income'], 0) . ' GHS'" color="emerald" />
            <x-ui.stat-card label="Expenses" :value="number_format($stats['expense'], 0) . ' GHS'" color="rose" />
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            {{-- User Info --}}
            <x-ui.card title="Account Details">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted">Email</span><span>{{ $user->email }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Phone</span><span>{{ $user->phone ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Roles</span>
                        <span>@foreach($user->roles as $role) <span class="px-2 py-0.5 rounded text-xs bg-purple-500/20 text-purple-300">{{ $role->name }}</span> @endforeach</span>
                    </div>
                    <div class="flex justify-between"><span class="text-muted">Joined</span><span>{{ $user->created_at->format('M j, Y \a\t g:i A') }}</span></div>
                </div>

                <div class="mt-4 pt-4 border-t border-border flex flex-wrap gap-3">
                    {{-- Impersonate --}}
                    <form action="{{ route('superadmin.users.impersonate', $user) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-600 text-white text-sm hover:bg-amber-500">Impersonate</button>
                    </form>

                    {{-- Edit --}}
                    <a href="{{ route('superadmin.users.edit', $user) }}" class="px-3 py-1.5 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-500">Edit</a>

                    {{-- Reset Password --}}
                    <form action="{{ route('superadmin.users.reset-password', $user) }}" method="POST" class="inline" data-confirm="Reset password for {{ $user->name }}?">
                        @csrf
                        <input type="hidden" name="password" value="password">
                        <input type="hidden" name="password_confirmation" value="password">
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-gray-600 text-white text-sm hover:bg-gray-500">Reset Password</button>
                    </form>

                    {{-- Delete --}}
                    <form action="{{ route('superadmin.users.destroy', $user) }}" method="POST" class="inline" data-confirm="Delete user {{ $user->name }}? This cannot be undone.">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-sm hover:bg-red-500">Delete</button>
                    </form>
                </div>
            </x-ui.card>

            {{-- Recent Tasks --}}
            <x-ui.card title="Recent Tasks">
                @if($recentTasks->isEmpty())
                    <p class="text-muted text-sm">No tasks.</p>
                @else
                    <div class="space-y-2">
                        @foreach($recentTasks as $task)
                            <div class="flex justify-between text-sm">
                                <span>{{ $task->title }}</span>
                                <span class="text-muted">{{ $task->status }} &middot; {{ $task->due_at?->format('M j') ?: '—' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
