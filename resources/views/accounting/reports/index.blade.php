<x-ui.page title="Financial Reports" description="Summary views — full IFRS reporting in a later phase.">
    <div class="grid sm:grid-cols-3 gap-4 mb-6">
        <x-ui.stat-card label="Total Income" :value="number_format($income, 2).' GHS'" />
        <x-ui.stat-card label="Total Expenses" :value="number_format($expense, 2).' GHS'" />
        <x-ui.stat-card label="Net Position" :value="number_format($income - $expense, 2).' GHS'" />
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <x-ui.card>
            <h3 class="font-semibold text-slate-900 mb-4">By category</h3>
            @forelse ($byCategory as $row)
                <div class="flex justify-between py-2 border-b border-slate-100 text-sm">
                    <span>{{ $row->category }} <x-ui.badge variant="{{ $row->type === 'income' ? 'success' : 'danger' }}" class="ml-1">{{ $row->type }}</x-ui.badge></span>
                    <span class="font-medium">{{ number_format($row->total, 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">No transaction data yet.</p>
            @endforelse
        </x-ui.card>

        <x-ui.card>
            <h3 class="font-semibold text-slate-900 mb-4">Active budgets</h3>
            @forelse ($budgets as $budget)
                <div class="py-2 border-b border-slate-100 text-sm">
                    <p class="font-medium">{{ $budget->name }}</p>
                    <p class="text-slate-500">{{ $budget->category }} · {{ number_format($budget->amount, 2) }} / {{ $budget->period }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">No budgets defined.</p>
            @endforelse
            <div class="mt-4">
                <x-ui.button :href="route('accounting.budgets.index')" variant="secondary" size="sm">Manage budgets</x-ui.button>
            </div>
        </x-ui.card>
    </div>
</x-ui.page>
