<x-ui.page title="Budgets" description="Plan and track financial spending limits.">
    <x-slot name="actions">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="search" placeholder="Search name, category..." value="{{ request('search') }}"
                   class="px-3 py-1.5 rounded-lg border border-[var(--border)] bg-[var(--surface)] text-xs focus:ring-2 focus:ring-purple-500/50 outline-none w-48">
            <x-ui.button type="submit" variant="secondary" size="sm">Filter</x-ui.button>
        </form>
        <x-ui.button :href="route('accounting.budgets.create')" variant="primary" size="sm">New Budget</x-ui.button>
    </x-slot>

    <x-ui.table-shell>
        <thead>
            <tr>
                <th class="py-3 font-semibold text-slate-500 text-left">Budget Name</th>
                <th class="py-3 font-semibold text-slate-500 text-left">Category</th>
                <th class="py-3 font-semibold text-slate-500 text-right">Limit Amount</th>
                <th class="py-3 font-semibold text-slate-500 text-center">Period</th>
                <th class="py-3 font-semibold text-slate-500 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($budgets as $budget)
                <tr class="border-b border-slate-100 hover:bg-slate-50/40 transition-colors">
                    <td class="py-4">
                        <p class="font-bold text-slate-800 text-xs">{{ $budget->name }}</p>
                        @if($budget->starts_at || $budget->ends_at)
                            <p class="text-[9px] text-slate-400 font-semibold mt-0.5">
                                {{ $budget->starts_at?->format('M j, Y') ?? 'Start' }} to {{ $budget->ends_at?->format('M j, Y') ?? 'End' }}
                            </p>
                        @endif
                    </td>
                    <td class="py-4 text-slate-600 font-semibold text-xs">{{ $budget->category }}</td>
                    <td class="py-4 text-right font-extrabold text-slate-800 text-xs">
                        {{ number_format($budget->amount, 2) }} GHS
                    </td>
                    <td class="py-4 text-center">
                        <x-ui.badge variant="primary" class="!py-0.5 !px-2.5 !text-[10px] font-bold">
                            {{ ucfirst($budget->period) }}
                        </x-ui.badge>
                    </td>
                    <td class="py-4">
                        <div class="flex gap-2 justify-center items-center">
                            <x-ui.button :href="route('accounting.budgets.edit', $budget)" variant="link" size="sm" class="!text-indigo-600 dark:!text-indigo-400">Edit</x-ui.button>
                            <span class="text-[var(--border)]">|</span>
                            <form method="POST" action="{{ route('accounting.budgets.destroy', $budget) }}" class="inline">
                                @csrf @method('DELETE')
                                <x-ui.button type="submit" variant="link" size="sm" class="!text-rose-600 dark:!text-rose-400" data-confirm="Delete budget?">Delete</x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-ui.empty-state title="No budgets created yet">
                            <x-slot name="action">
                                <x-ui.button :href="route('accounting.budgets.create')" variant="primary" size="sm">Create budget</x-ui.button>
                            </x-slot>
                        </x-ui.empty-state>
                    </td>
                </tr>
            @endforelse
        </tbody>
        <x-slot name="footer">{{ $budgets->links() }}</x-slot>
    </x-ui.table-shell>
</x-ui.page>
