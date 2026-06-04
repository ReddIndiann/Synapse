<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <div>
        <x-input-label for="name" value="Budget name" />
        <x-text-input id="name" class="mt-1" name="name" :value="old('name', $budget?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="category" value="Category" />
            <x-text-input id="category" class="mt-1" name="category" :value="old('category', $budget?->category)" required />
            <x-input-error :messages="$errors->get('category')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="amount" value="Limit amount" />
            <x-text-input id="amount" class="mt-1" type="number" step="0.01" name="amount" :value="old('amount', $budget?->amount)" required />
            <x-input-error :messages="$errors->get('amount')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="period" value="Period" />
        <select id="period" name="period" class="auth-input mt-1">
            @foreach ($periods as $p)
                <option value="{{ $p }}" @selected(old('period', $budget?->period ?? 'monthly') === $p)>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('period')" class="mt-1" />
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="starts_at" value="Starts" />
            <x-text-input id="starts_at" class="mt-1" type="date" name="starts_at" :value="old('starts_at', $budget?->starts_at?->format('Y-m-d'))" />
            <x-input-error :messages="$errors->get('starts_at')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="ends_at" value="Ends" />
            <x-text-input id="ends_at" class="mt-1" type="date" name="ends_at" :value="old('ends_at', $budget?->ends_at?->format('Y-m-d'))" />
            <x-input-error :messages="$errors->get('ends_at')" class="mt-1" />
        </div>
    </div>

    <div class="flex gap-3 pt-2">
        <x-primary-button>{{ $budget ? 'Update' : 'Create' }}</x-primary-button>
        <x-ui.button :href="route('accounting.budgets.index')" variant="ghost" size="sm">Cancel</x-ui.button>
    </div>
</form>
