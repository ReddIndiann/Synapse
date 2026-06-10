<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">Services</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="grid md:grid-cols-2 gap-6">
            {{-- Queue --}}
            <x-ui.card title="Queue">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted">Driver</span><span>{{ $queue['driver'] }}</span></div>
                    <div class="flex justify-between">
                        <span class="text-muted">Pending Jobs</span>
                        <span class="{{ $queue['pending'] > 0 ? 'text-amber-400' : 'text-emerald-400' }}">{{ $queue['pending'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted">Failed Jobs</span>
                        <span class="{{ $queue['failed'] > 0 ? 'text-red-400' : 'text-emerald-400' }}">{{ $queue['failed'] }}</span>
                    </div>
                    <div class="flex gap-2 mt-3 pt-3 border-t border-border">
                        <form action="{{ route('superadmin.services.retry-failed-jobs') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-2 py-1 rounded bg-amber-600 text-white text-xs hover:bg-amber-500">Retry Failed</button>
                        </form>
                        <form action="{{ route('superadmin.services.purge-failed-jobs') }}" method="POST" class="inline" data-confirm="Purge all failed jobs?">
                            @csrf
                            <button type="submit" class="px-2 py-1 rounded bg-red-600 text-white text-xs hover:bg-red-500">Purge Failed</button>
                        </form>
                    </div>
                </div>
            </x-ui.card>

            {{-- Cache --}}
            <x-ui.card title="Cache">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted">Driver</span><span>{{ $cache['driver'] }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Store</span><span class="text-xs">{{ $cache['store'] }}</span></div>
                    <div class="flex gap-2 mt-3 pt-3 border-t border-border">
                        <form action="{{ route('superadmin.services.clear-cache') }}" method="POST" class="inline" data-confirm="Clear all caches?">
                            @csrf
                            <button type="submit" class="px-2 py-1 rounded bg-amber-600 text-white text-xs hover:bg-amber-500">Clear All Caches</button>
                        </form>
                        <form action="{{ route('superadmin.services.optimize') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-2 py-1 rounded bg-purple-600 text-white text-xs hover:bg-purple-500">Optimize</button>
                        </form>
                    </div>
                </div>
            </x-ui.card>

            {{-- Database --}}
            <x-ui.card title="Database">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted">Connection</span><span>{{ $database['connection'] }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Driver</span><span>{{ $database['driver'] }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Name</span><span>{{ $database['name'] }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Migrations Run</span><span>{{ $migrations }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Migration Batch</span><span>{{ $migrationBatch }}</span></div>
                </div>
            </x-ui.card>

            {{-- Storage --}}
            <x-ui.card title="Storage">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted">Default Disk</span><span>{{ $storage['disk'] }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Public Files</span><span>{{ $storage['public_files'] }}</span></div>
                </div>
            </x-ui.card>
        </div>

        {{-- Recent Publish Jobs --}}
        <x-ui.card title="Recent Publish Jobs">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-muted border-b border-border">
                            <th class="text-left py-2 px-2">ID</th>
                            <th class="text-left py-2 px-2">User</th>
                            <th class="text-left py-2 px-2">Status</th>
                            <th class="text-right py-2 px-2">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($publishJobs as $job)
                            <tr class="border-b border-border/50">
                                <td class="py-2 px-2">#{{ $job->id }}</td>
                                <td class="py-2 px-2">{{ $job->user?->name ?? '—' }}</td>
                                <td class="py-2 px-2">{{ $job->status }}</td>
                                <td class="py-2 px-2 text-right text-muted">{{ $job->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
