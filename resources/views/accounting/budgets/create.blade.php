<x-ui.page title="Create Budget" maxWidth="3xl">
    <x-ui.form-card title="Budget details">
        @include('accounting.budgets.partials.form', ['action' => route('accounting.budgets.store'), 'method' => 'POST', 'budget' => null, 'periods' => $periods])
    </x-ui.form-card>
</x-ui.page>
