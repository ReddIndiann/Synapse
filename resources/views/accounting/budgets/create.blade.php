<x-ui.page title="Create Budget" maxWidth="3xl">
    <x-ui.back-link :href="route('accounting.budgets.index')" label="Back to Budgets" />
    <x-ui.form-card title="Budget details">
        @include('accounting.budgets.partials.form', ['action' => route('accounting.budgets.store'), 'method' => 'POST', 'budget' => null, 'periods' => $periods, 'categories' => $categories])
    </x-ui.form-card>
</x-ui.page>
