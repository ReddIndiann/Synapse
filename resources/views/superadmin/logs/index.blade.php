<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl leading-tight">Logs</h2>
            <div class="flex gap-2">
                <form method="GET" class="flex gap-2">
                    <select name="level" class="px-3 py-1.5 rounded-lg border border-border bg-surface text-sm" onchange="this.form.submit()">
                        <option value="">All Levels</option>
                        @foreach($levels as $lv)
                            <option value="{{ $lv }}" {{ request('level') === $lv ? 'selected' : '' }}>{{ $lv }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="search" placeholder="Search logs..." value="{{ request('search') }}"
                           class="px-3 py-1.5 rounded-lg border border-border bg-surface text-sm">
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-purple-600 text-white text-sm">Filter</button>
                </form>
                <form action="{{ route('superadmin.logs.clear') }}" method="POST" data-confirm="Clear all logs?" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-sm hover:bg-red-500">Clear</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-ui.card>
            @if(empty($logContent))
                <p class="text-muted text-sm">No log entries match your criteria.</p>
            @else
                <div class="space-y-1 text-xs font-mono max-h-[70vh] overflow-y-auto">
                    @foreach($logContent as $entry)
                        @php
                            $colors = [
                                'ERROR' => 'text-red-400',
                                'CRITICAL' => 'text-red-300',
                                'ALERT' => 'text-red-200',
                                'WARNING' => 'text-amber-400',
                                'DEBUG' => 'text-gray-500',
                                'INFO' => 'text-emerald-400',
                            ];
                            $color = $colors[$entry['level']] ?? 'text-muted';
                        @endphp
                        <div class="{{ $color }} leading-relaxed">
                            <span class="opacity-60">[{{ $entry['timestamp'] }}]</span>
                            <span class="font-semibold">{{ $entry['level'] }}</span>
                            <span>{{ $entry['text'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>
