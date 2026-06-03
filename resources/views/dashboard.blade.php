<x-ui.page title="Dashboard" description="Your Synapse workspace at a glance.">
    <!-- Stat Cards Summary Row -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        <x-ui.stat-card label="Open Tasks" :value="$openTasks" hint="Intelligent active checklist" />
        <x-ui.stat-card label="Net Balance" :value="number_format($income - $expense, 2).' GHS'" hint="Double-entry ledger sum" />
        <x-ui.stat-card label="Media Assets" :value="$mediaCount" hint="Ready for platform distribution" />
    </div>

    <!-- Core Workspaces Row -->
    <div class="grid lg:grid-cols-3 gap-5 mb-8">
        <x-ui.card class="relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/5 rounded-full blur-xl group-hover:bg-indigo-500/10 transition-all"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <h3 class="font-bold text-slate-800 text-base">AI Assistant</h3>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed mb-5">Command processing, smart timing, and conflict resolution workspace.</p>
            <div class="flex items-center gap-2">
                <x-ui.button :href="route('assistant.chat')" variant="primary" size="sm" class="shadow-sm">Open Chat</x-ui.button>
                <x-ui.button :href="route('assistant.tasks.index')" variant="secondary" size="sm">Tasks</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card class="relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-full blur-xl group-hover:bg-emerald-500/10 transition-all"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="font-bold text-slate-800 text-base">Accountant</h3>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed mb-5">Double-entry general ledger, IFRS compliance, and reporting metrics.</p>
            <div class="flex items-center gap-2">
                <x-ui.button :href="route('accounting.transactions.index')" variant="primary" size="sm" class="shadow-sm">Transactions</x-ui.button>
                <x-ui.button :href="route('accounting.reports.index')" variant="secondary" size="sm">Reports</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card class="relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-sky-500/5 rounded-full blur-xl group-hover:bg-sky-500/10 transition-all"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="font-bold text-slate-800 text-base">Project Distributor</h3>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed mb-5">Queue uploads, transcode audio/video, and distribute to channels.</p>
            <div class="flex items-center gap-2">
                <x-ui.button :href="route('distribution.media.index')" variant="primary" size="sm" class="shadow-sm">Media Library</x-ui.button>
                <x-ui.button :href="route('distribution.publish.index')" variant="secondary" size="sm">Publish Queue</x-ui.button>
            </div>
        </x-ui.card>
    </div>

    <!-- Active Feeds Row -->
    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Recent Tasks Feed -->
        <x-ui.card>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="font-bold text-slate-800 text-sm tracking-tight uppercase">Recent Tasks</h3>
                <x-ui.button :href="route('assistant.tasks.index')" variant="link" size="sm" class="!text-xs">View all</x-ui.button>
            </div>
            <div class="space-y-2.5 max-h-[300px] overflow-y-auto pr-1">
                @forelse ($recentTasks as $task)
                    <div class="flex items-center justify-between p-3 rounded-2xl border border-slate-100/80 bg-slate-50/40 hover:bg-slate-50/90 transition-all">
                        <div class="flex items-center gap-3">
                            <!-- Visual Checkbox indicator -->
                            <div class="w-4.5 h-4.5 rounded-md border border-slate-300 flex items-center justify-center shrink-0 text-white bg-white/40
                                @if($task->status === 'completed') !bg-indigo-600 !border-indigo-600 @endif">
                                @if($task->status === 'completed')
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </div>
                            <span class="text-xs font-semibold text-slate-700 truncate max-w-[200px] sm:max-w-xs @if($task->status === 'completed') line-through text-slate-400 @endif">
                                {{ $task->title }}
                            </span>
                        </div>
                        <x-ui.badge variant="{{ $task->status === 'completed' ? 'success' : ($task->priority === 'high' ? 'danger' : 'primary') }}" class="!py-0.5 !px-2.5 !text-[10px] font-bold">
                            {{ $task->status === 'completed' ? 'completed' : $task->priority }}
                        </x-ui.badge>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400 font-medium">No tasks captured yet.</div>
                @endforelse
            </div>
        </x-ui.card>

        <!-- Recent Transactions Feed -->
        <x-ui.card>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="font-bold text-slate-800 text-sm tracking-tight uppercase">Recent Transactions</h3>
                <x-ui.button :href="route('accounting.transactions.index')" variant="link" size="sm" class="!text-xs">View all</x-ui.button>
            </div>
            <div class="space-y-2.5 max-h-[300px] overflow-y-auto pr-1">
                @forelse ($recentTransactions as $tx)
                    <div class="flex items-center justify-between p-3 rounded-2xl border border-slate-100/80 bg-slate-50/40 hover:bg-slate-50/90 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 font-bold text-xs
                                @if($tx->type === 'income') bg-emerald-50 text-emerald-600 @else bg-rose-50 text-rose-600 @endif">
                                {{ $tx->type === 'income' ? 'IN' : 'OUT' }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-700 truncate max-w-[150px] sm:max-w-xs">{{ $tx->category }}</p>
                                <p class="text-[10px] text-slate-400 font-medium">{{ $tx->occurred_at->format('M d, Y') }} · {{ $tx->payment_method }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold tracking-tight shrink-0
                            @if($tx->type === 'income') text-emerald-600 @else text-rose-600 @endif">
                            {{ $tx->type === 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2) }} {{ $tx->currency }}
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400 font-medium">No transactions recorded yet.</div>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-ui.page>
