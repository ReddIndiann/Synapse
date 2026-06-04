<x-ui.page title="Transactions" description="Double-entry financial ledger records.">
    <x-slot name="actions">
        <x-ui.button :href="route('accounting.transactions.create')" variant="primary" size="sm">New Transaction</x-ui.button>
    </x-slot>

    <!-- Stat Summaries -->
    <div class="grid sm:grid-cols-3 gap-5 mb-8">
        <x-ui.stat-card label="Ledger Income" :value="number_format($income, 2).' GHS'" hint="Total base receipts" />
        <x-ui.stat-card label="Ledger Expenses" :value="number_format($expense, 2).' GHS'" hint="Total base payments" />
        <x-ui.stat-card label="Net Position" :value="number_format($income - $expense, 2).' GHS'" hint="Asset balance check" />
    </div>

    <!-- Table List -->
    <x-ui.table-shell>
        <thead>
            <tr>
                <th class="py-3 font-semibold text-slate-500 text-left">Date</th>
                <th class="py-3 font-semibold text-slate-500 text-left">Type</th>
                <th class="py-3 font-semibold text-slate-500 text-left">Category / Method</th>
                <th class="py-3 font-semibold text-slate-500 text-right">Ledger Amount</th>
                <th class="py-3 font-semibold text-slate-500 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $tx)
                <tr class="border-b border-slate-100 hover:bg-slate-50/40 transition-colors">
                    <td class="py-4 text-slate-600 font-medium text-xs">{{ $tx->occurred_at->format('M d, Y') }}</td>
                    <td class="py-4">
                        <x-ui.badge variant="{{ $tx->type === 'income' ? 'success' : 'danger' }}" class="!py-0.5 !px-2.5 !text-[10px] font-bold">
                            {{ $tx->type }}
                        </x-ui.badge>
                    </td>
                    <td class="py-4">
                        <p class="font-bold text-slate-800 text-xs">{{ $tx->category }}</p>
                        <p class="text-[10px] text-slate-400 font-semibold">{{ $tx->payment_method }} @if($tx->reference) · Ref: {{ $tx->reference }} @endif</p>
                    </td>
                    <td class="py-4 text-right">
                        @php $baseAmount = $tx->amount * $tx->exchange_rate; @endphp
                        <p class="font-extrabold text-slate-800 text-xs @if($tx->type === 'income') text-emerald-600 @else text-rose-600 @endif">
                            {{ $tx->type === 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2) }} {{ $tx->currency }}
                        </p>
                        @if($tx->currency !== 'GHS')
                            <p class="text-[9px] text-slate-400 font-medium">({{ number_format($baseAmount, 2) }} GHS @ {{ number_format($tx->exchange_rate, 2) }})</p>
                        @endif
                    </td>
                    <td class="py-4">
                        <div class="flex gap-2 justify-center items-center">
                            <x-ui.button :href="route('accounting.transactions.edit', $tx)" variant="link" size="sm" class="!text-indigo-600 dark:!text-indigo-400">Edit</x-ui.button>
                            <span class="text-[var(--border)]">|</span>
                            <form method="POST" action="{{ route('accounting.transactions.destroy', $tx) }}" class="inline">
                                @csrf @method('DELETE')
                                <x-ui.button type="submit" variant="link" size="sm" class="!text-rose-600 dark:!text-rose-400" onclick="return confirm('Delete transaction?')">Delete</x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-ui.empty-state title="No transactions recorded yet">
                            <x-slot name="action">
                                <x-ui.button :href="route('accounting.transactions.create')" variant="primary" size="sm">Add transaction</x-ui.button>
                            </x-slot>
                        </x-ui.empty-state>
                    </td>
                </tr>
            @endforelse
        </tbody>
        <x-slot name="footer">{{ $transactions->links() }}</x-slot>
    </x-ui.table-shell>
</x-ui.page>
