<x-ui.page title="Record Transaction" maxWidth="3xl">
    <x-ui.form-card title="Transaction details">
        @include('accounting.transactions.partials.form', ['action' => route('accounting.transactions.store'), 'method' => 'POST', 'transaction' => null, 'types' => $types])
    </x-ui.form-card>
</x-ui.page>
