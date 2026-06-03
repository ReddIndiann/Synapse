<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="type" value="Type" />
            <select id="type" name="type" class="auth-input mt-1">
                @foreach ($types as $t)
                    <option value="{{ $t }}" @selected(old('type', $transaction?->type) === $t)>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('type')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="amount" value="Amount" />
            <x-text-input id="amount" class="mt-1" type="number" step="0.01" min="0.01" name="amount" :value="old('amount', $transaction?->amount)" required />
            <x-input-error :messages="$errors->get('amount')" class="mt-1" />
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="currency" value="Currency" />
            <x-text-input id="currency" class="mt-1" name="currency" maxlength="3" :value="old('currency', $transaction?->currency ?? 'GHS')" required />
            <x-input-error :messages="$errors->get('currency')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="occurred_at" value="Date" />
            <x-text-input id="occurred_at" class="mt-1" type="date" name="occurred_at" :value="old('occurred_at', $transaction?->occurred_at?->format('Y-m-d') ?? date('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('occurred_at')" class="mt-1" />
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="payment_method" value="Payment Method (Asset Account)" />
            <select id="payment_method" name="payment_method" class="auth-input mt-1">
                <option value="Cash" @selected(old('payment_method', $transaction?->payment_method ?? 'Cash') === 'Cash')>Cash (1000)</option>
                <option value="Bank" @selected(old('payment_method', $transaction?->payment_method) === 'Bank')>Bank Account (1010)</option>
                <option value="Mobile Money" @selected(old('payment_method', $transaction?->payment_method) === 'Mobile Money')>Mobile Money (1020)</option>
            </select>
            <x-input-error :messages="$errors->get('payment_method')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="exchange_rate" value="Exchange Rate (GHS per unit of Currency)" />
            <x-text-input id="exchange_rate" class="mt-1" type="number" step="0.000001" min="0.000001" name="exchange_rate" :value="old('exchange_rate', $transaction?->exchange_rate ?? '1.000000')" required />
            <x-input-error :messages="$errors->get('exchange_rate')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="category" value="Category" />
        <x-text-input id="category" class="mt-1" name="category" placeholder="e.g. Consulting Revenue, Rent Expense, Software Subscriptions" :value="old('category', $transaction?->category)" required />
        <x-input-error :messages="$errors->get('category')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="description" value="Description" />
        <x-text-input id="description" class="mt-1" name="description" :value="old('description', $transaction?->description)" />
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="reference" value="Reference" />
        <x-text-input id="reference" class="mt-1" name="reference" :value="old('reference', $transaction?->reference)" />
        <x-input-error :messages="$errors->get('reference')" class="mt-1" />
    </div>

    <div class="flex gap-3 pt-2">
        <x-primary-button>{{ $transaction ? 'Update' : 'Save' }}</x-primary-button>
        <x-ui.button :href="route('accounting.transactions.index')" variant="ghost" size="sm">Cancel</x-ui.button>
    </div>
</form>
