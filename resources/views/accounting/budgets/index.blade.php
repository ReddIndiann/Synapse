<x-ui.page title="Budgets" description="Plan and track spending limits.">
    <x-slot name="actions">
        <x-ui.button :href="route('accounting.budgets.create')" variant="primary" size="sm">New Budget</x-ui.button>
    </x-slot>

    <x-ui.table-shell>
        <thead>
            <tr>
                <th class="py-2 font-medium">Name</th>
                <th class="py-2 font-medium">Category</th>
                <th class="py-2 font-medium">Amount</th>
                <th class="py-2 font-medium">Period</th>
                <th class="py-2 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($budgets as $budget)
                <tr>
                    <td class="py-3 font-medium">{{ $budget->name }}</td>
                    <td class="py-3">{{ $budget->category }}</td>
                    <td class="py-3">{{ number_format($budget->amount, 2) }}</td>
                    <td class="py-3"><x-ui.badge>{{ $budget->period }}</x-ui.badge></td>
                    <td class="py-2">
                        <div class="flex gap-3">
                            <x-ui.button :href="route('accounting.budgets.edit', $budget)" variant="link" size="sm">Edit</x-ui.button>
                            <form method="POST" action="{{ route('accounting.budgets.destroy', $budget) }}">@csrf @method('DELETE')
                                <x-ui.button type="submit" variant="link" size="sm" class="!text-red-600" onclick="return confirm('Delete?')">Delete</x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-ui.empty-state title="No budgets"><x-slot name="action"><x-ui.button :href="route('accounting.budgets.create')" variant="primary" size="sm">Create budget</x-ui.button></x-slot></x-ui.empty-state></td></tr>
            @endforelse
        </tbody>
        <x-slot name="footer">{{ $budgets->links() }}</x-slot>
    </x-ui.table-shell>
</x-ui.page>
