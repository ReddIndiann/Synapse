<x-ui.page title="Transactions" description="Income and expense records.">
    <x-slot name="actions">
        <x-ui.button :href="route('accounting.transactions.create')" variant="primary" size="sm">New Transaction</x-ui.button>
    </x-slot>

    <div class="grid sm:grid-cols-3 gap-4 mb-6">
        <x-ui.stat-card label="Income" :value="number_format($income, 2).' GHS'" />
        <x-ui.stat-card label="Expenses" :value="number_format($expense, 2).' GHS'" />
        <x-ui.stat-card label="Net" :value="number_format($income - $expense, 2).' GHS'" />
    </div>

    <x-ui.table-shell>
        <thead>
            <tr>
                <th class="py-2 font-medium">Date</th>
                <th class="py-2 font-medium">Type</th>
                <th class="py-2 font-medium">Category</th>
                <th class="py-2 font-medium">Amount</th>
                <th class="py-2 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $tx)
                <tr>
                    <td class="py-3">{{ $tx->occurred_at->format('M j, Y') }}</td>
                    <td class="py-3"><x-ui.badge variant="{{ $tx->type === 'income' ? 'success' : 'danger' }}">{{ $tx->type }}</x-ui.badge></td>
                    <td class="py-3">{{ $tx->category }}</td>
                    <td class="py-3 font-medium">{{ number_format($tx->amount, 2) }} {{ $tx->currency }}</td>
                    <td class="py-2">
                        <div class="flex gap-3">
                            <x-ui.button :href="route('accounting.transactions.edit', $tx)" variant="link" size="sm">Edit</x-ui.button>
                            <form method="POST" action="{{ route('accounting.transactions.destroy', $tx) }}">@csrf @method('DELETE')
                                <x-ui.button type="submit" variant="link" size="sm" class="!text-red-600" onclick="return confirm('Delete?')">Delete</x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-ui.empty-state title="No transactions"><x-slot name="action"><x-ui.button :href="route('accounting.transactions.create')" variant="primary" size="sm">Add transaction</x-ui.button></x-slot></x-ui.empty-state></td></tr>
            @endforelse
        </tbody>
        <x-slot name="footer">{{ $transactions->links() }}</x-slot>
    </x-ui.table-shell>
</x-ui.page>
