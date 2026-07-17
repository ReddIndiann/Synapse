<x-ui.page title="Edit Budget" maxWidth="3xl">
    <x-ui.back-link :href="route('accounting.budgets.index')" label="Back to Budgets" />
    <x-ui.form-card title="Budget details">
        @include('accounting.budgets.partials.form', ['action' => route('accounting.budgets.update', $budget), 'method' => 'PUT', 'budget' => $budget, 'periods' => $periods, 'categories' => $categories])
    </x-ui.form-card>
</x-ui.page>
