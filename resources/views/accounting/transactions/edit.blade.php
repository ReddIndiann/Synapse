<x-ui.page title="Edit Transaction" maxWidth="3xl">
    <x-ui.back-link :href="route('accounting.transactions.index')" label="Back to Transactions" />
    <x-ui.form-card title="Transaction details">
        @include('accounting.transactions.partials.form', ['action' => route('accounting.transactions.update', $transaction), 'method' => 'PUT', 'transaction' => $transaction, 'types' => $types])
    </x-ui.form-card>
</x-ui.page>
