@php use Illuminate\Support\Str; @endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">SuperAdmin Dashboard</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <x-ui.stat-card label="Users" :value="$stats['users']" color="violet" />
            <x-ui.stat-card label="Tasks" :value="$stats['tasks']" color="indigo" />
            <x-ui.stat-card label="Transactions" :value="$stats['transactions']" color="emerald" />
            <x-ui.stat-card label="Budgets" :value="$stats['budgets']" color="amber" />
            <x-ui.stat-card label="Media" :value="$stats['media']" color="sky" />
            <x-ui.stat-card label="Publish Jobs" :value="$stats['publish_jobs']" color="rose" />
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            {{-- System Information --}}
            <x-ui.card title="System">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted">PHP</span><span>{{ $system['php_version'] }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Laravel</span><span>{{ $system['laravel_version'] }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Database</span><span>{{ $system['db_connection'] }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Cache Driver</span><span>{{ $system['cache_driver'] }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Queue Driver</span><span>{{ $system['queue_driver'] }}</span></div>
                    <div class="flex justify-between">
                        <span class="text-muted">Queue Size</span>
                        <span class="{{ $system['queue_size'] > 0 ? 'text-amber-400' : 'text-emerald-400' }}">{{ $system['queue_size'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted">Failed Jobs</span>
                        <span class="{{ $system['failed_jobs'] > 0 ? 'text-red-400' : 'text-emerald-400' }}">{{ $system['failed_jobs'] }}</span>
                    </div>
                </div>
            </x-ui.card>

            {{-- AI Status --}}
            <x-ui.card title="AI Provider">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted">Active Provider</span>
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-purple-500/20 text-purple-300">
                            {{ $ai['provider'] }}
                        </span>
                    </div>
                    @if(!empty($ai['fallback_providers']))
                        <div class="flex justify-between">
                            <span class="text-muted">Fallbacks</span>
                            <span class="text-xs">{{ implode(', ', $ai['fallback_providers']) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-muted">Configured APIs</span>
                        <span class="text-xs text-right max-w-[200px]">
                            {{ count($ai['configured_providers']) ? implode(', ', $ai['configured_providers']) : 'None (using regex fallback)' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted">Local AI</span>
                        <span class="{{ $ai['local_available'] ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ $ai['local_available'] ? 'Online' : 'Unreachable' }}
                        </span>
                    </div>
                    @if($ai['local_model'])
                        <div class="flex justify-between">
                            <span class="text-muted">Local Model</span>
                            <span>{{ $ai['local_model'] }}</span>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>

        {{-- Recent Logs --}}
        <x-ui.card title="Recent Logs">
            @if(empty($recentLogs))
                <p class="text-muted text-sm">No log entries.</p>
            @else
                <div class="space-y-1 max-h-64 overflow-y-auto text-xs font-mono">
                    @foreach($recentLogs as $log)
                        @php
                            $isError = Str::contains($log, '.ERROR');
                            $isWarning = Str::contains($log, '.WARNING');
                        @endphp
                        <div class="{{ $isError ? 'text-red-400' : ($isWarning ? 'text-amber-400' : 'text-muted') }} truncate">
                            {{ trim($log) }}
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>
