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
        </div>
        <div>
            <x-input-label for="amount" value="Amount" />
            <x-text-input id="amount" class="mt-1" type="number" step="0.01" min="0.01" name="amount" :value="old('amount', $transaction?->amount)" required />
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="currency" value="Currency" />
            <x-text-input id="currency" class="mt-1" name="currency" maxlength="3" :value="old('currency', $transaction?->currency ?? 'GHS')" required />
        </div>
        <div>
            <x-input-label for="occurred_at" value="Date" />
            <x-text-input id="occurred_at" class="mt-1" type="date" name="occurred_at" :value="old('occurred_at', $transaction?->occurred_at?->format('Y-m-d') ?? date('Y-m-d'))" required />
        </div>
    </div>

    <div>
        <x-input-label for="category" value="Category" />
        <x-text-input id="category" class="mt-1" name="category" placeholder="e.g. Sales, Rent, Marketing" :value="old('category', $transaction?->category)" required />
    </div>

    <div>
        <x-input-label for="description" value="Description" />
        <x-text-input id="description" class="mt-1" name="description" :value="old('description', $transaction?->description)" />
    </div>

    <div>
        <x-input-label for="reference" value="Reference" />
        <x-text-input id="reference" class="mt-1" name="reference" :value="old('reference', $transaction?->reference)" />
    </div>

    <div class="flex gap-3">
        <x-primary-button>{{ $transaction ? 'Update' : 'Save' }}</x-primary-button>
        <x-ui.button :href="route('accounting.transactions.index')" variant="ghost" size="sm">Cancel</x-ui.button>
    </div>
</form>
