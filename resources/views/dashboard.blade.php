<x-ui.page title="Dashboard" description="Your Synapse workspace at a glance.">
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <x-ui.stat-card label="Open Tasks" :value="$openTasks" hint="AI Assistant module" />
        <x-ui.stat-card label="Net Balance" :value="number_format($income - $expense, 2).' GHS'" hint="Income minus expenses" />
        <x-ui.stat-card label="Media Assets" :value="$mediaCount" hint="$pendingPublishes pending publishes" />
    </div>

    <div class="grid lg:grid-cols-3 gap-4 mb-8">
        <x-ui.card>
            <h3 class="font-semibold text-slate-900 mb-3">AI Assistant</h3>
            <p class="text-sm text-slate-600 mb-4">Tasks, scheduling, and natural-language commands.</p>
            <div class="flex flex-wrap gap-2">
                <x-ui.button :href="route('assistant.chat')" variant="primary" size="sm">Open Chat</x-ui.button>
                <x-ui.button :href="route('assistant.tasks.index')" variant="secondary" size="sm">Tasks</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card>
            <h3 class="font-semibold text-slate-900 mb-3">Accounting</h3>
            <p class="text-sm text-slate-600 mb-4">{{ $budgetCount }} budgets · {{ number_format($income, 2) }} income · {{ number_format($expense, 2) }} expense</p>
            <div class="flex flex-wrap gap-2">
                <x-ui.button :href="route('accounting.transactions.index')" variant="primary" size="sm">Transactions</x-ui.button>
                <x-ui.button :href="route('accounting.reports.index')" variant="secondary" size="sm">Reports</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card>
            <h3 class="font-semibold text-slate-900 mb-3">Distribution</h3>
            <p class="text-sm text-slate-600 mb-4">Upload media and queue multi-platform publishing.</p>
            <div class="flex flex-wrap gap-2">
                <x-ui.button :href="route('distribution.media.index')" variant="primary" size="sm">Media Library</x-ui.button>
                <x-ui.button :href="route('distribution.publish.index')" variant="secondary" size="sm">Publish Queue</x-ui.button>
            </div>
        </x-ui.card>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <x-ui.card>
            <h3 class="font-semibold text-slate-900 mb-4">Recent Tasks</h3>
            @forelse ($recentTasks as $task)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                    <span class="text-sm text-slate-800">{{ $task->title }}</span>
                    <x-ui.badge variant="{{ $task->status === 'completed' ? 'success' : 'primary' }}">{{ $task->status }}</x-ui.badge>
                </div>
            @empty
                <p class="text-sm text-slate-500">No tasks yet.</p>
            @endforelse
        </x-ui.card>

        <x-ui.card>
            <h3 class="font-semibold text-slate-900 mb-4">Recent Transactions</h3>
            @forelse ($recentTransactions as $tx)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                    <span class="text-sm text-slate-800">{{ $tx->category }}</span>
                    <span class="text-sm font-medium {{ $tx->type === 'income' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $tx->type === 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2) }} {{ $tx->currency }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-slate-500">No transactions yet.</p>
            @endforelse
        </x-ui.card>
    </div>
</x-ui.page>
