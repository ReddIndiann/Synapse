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
            <div class="flex gap-2 items-center mt-1">
                <x-text-input id="currency" class="flex-1 !mt-0" name="currency" maxlength="3" :value="old('currency', $transaction?->currency ?? 'GHS')" required />
                <button type="button" id="btn-fetch-rate" class="px-3 py-2.5 text-xs font-semibold rounded-xl border border-[var(--border)] bg-[var(--surface)] text-[var(--text)] hover:bg-[var(--bg3)]/50 hover:text-[var(--pur)] transition-colors focus:outline-none flex items-center gap-1.5 shrink-0 h-[42px]">
                    <svg id="fetch-spinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Fetch Rate</span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('currency')" class="mt-1" />
            <span id="rate-feedback" class="text-[10px] text-[var(--text-muted)] mt-1.5 block"></span>
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
        <x-ui.category-select
            :value="old('category', $transaction?->category)"
            filter-by-type
            type-field-id="type"
        />
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const currencyInput = document.getElementById('currency');
    const exchangeRateInput = document.getElementById('exchange_rate');
    const btnFetchRate = document.getElementById('btn-fetch-rate');
    const spinner = document.getElementById('fetch-spinner');
    const feedback = document.getElementById('rate-feedback');

    async function fetchRate() {
        const currency = currencyInput.value.trim().toUpperCase();
        if (!currency || currency.length !== 3) {
            feedback.textContent = 'Please enter a valid 3-letter currency code (e.g., USD, EUR).';
            feedback.className = 'text-[10px] text-rose-400 mt-1 block';
            return;
        }

        if (currency === 'GHS') {
            exchangeRateInput.value = '1.000000';
            feedback.textContent = 'GHS to GHS rate is always 1.000000';
            feedback.className = 'text-[10px] text-[var(--text-secondary)] mt-1.5 block';
            return;
        }

        // Show spinner
        spinner.classList.remove('hidden');
        btnFetchRate.disabled = true;
        feedback.textContent = 'Fetching exchange rate...';
        feedback.className = 'text-[10px] text-[var(--text-secondary)] mt-1.5 block';

        try {
            const response = await fetch(`https://open.er-api.com/v6/latest/${currency}`);
            if (!response.ok) throw new Error('API request failed');
            
            const data = await response.json();
            if (data.result === 'success' && data.rates && data.rates.GHS) {
                const ghsRate = data.rates.GHS;
                exchangeRateInput.value = parseFloat(ghsRate).toFixed(6);
                feedback.textContent = `Live rate: 1 ${currency} = ${parseFloat(ghsRate).toFixed(4)} GHS (updated ${new Date().toLocaleTimeString()})`;
                feedback.className = 'text-[10px] text-emerald-400 mt-1.5 block';
            } else {
                throw new Error('GHS rate not found in response');
            }
        } catch (error) {
            console.error(error);
            feedback.textContent = 'Failed to fetch live rate. Please enter it manually.';
            feedback.className = 'text-[10px] text-rose-400 mt-1.5 block';
        } finally {
            spinner.classList.add('hidden');
            btnFetchRate.disabled = false;
        }
    }

    btnFetchRate.addEventListener('click', fetchRate);

    // Auto-fetch on input change if it looks like a valid code
    currencyInput.addEventListener('change', function() {
        const val = currencyInput.value.trim().toUpperCase();
        if (val.length === 3) {
            fetchRate();
        }
    });
});
</script>
