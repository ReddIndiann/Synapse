<x-ui.page title="Financial Reports" description="Ledger bookkeeping reports compiled in accordance with IFRS guidelines.">
    <x-slot name="actions">
        @if ($trialBalance['is_balanced'])
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 text-xs font-semibold text-emerald-500 shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                ✓ IFRS Compliant: Ledger Balanced
            </span>
        @else
            <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 border border-amber-500/20 px-3 py-1 text-xs font-semibold text-amber-500 shadow-sm animate-pulse">
                ⚠ Trial Balance Out of Balance
            </span>
        @endif
    </x-slot>

    <!-- Navigation Tabs -->
    <div class="border-b border-[var(--border)] mb-6">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button onclick="switchTab('overview')" id="overview-tab" role="tab" class="border-indigo-600 text-indigo-600 dark:text-indigo-400 whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm transition-all focus:outline-none">
                Overview
            </button>
            <button onclick="switchTab('pnl')" id="pnl-tab" role="tab" class="border-transparent text-[var(--text-secondary)] hover:text-[var(--text)] hover:border-[var(--text-muted)] whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-all focus:outline-none">
                Profit & Loss (Income Statement)
            </button>
            <button onclick="switchTab('balancesheet')" id="balancesheet-tab" role="tab" class="border-transparent text-[var(--text-secondary)] hover:text-[var(--text)] hover:border-[var(--text-muted)] whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-all focus:outline-none">
                Balance Sheet
            </button>
            <button onclick="switchTab('trialbalance')" id="trialbalance-tab" role="tab" class="border-transparent text-[var(--text-secondary)] hover:text-[var(--text)] hover:border-[var(--text-muted)] whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-all focus:outline-none">
                Trial Balance
            </button>
        </nav>
    </div>

    <!-- Overview Tab Panel -->
    <div id="overview-panel" role="tabpanel" class="space-y-6">
        <div class="grid sm:grid-cols-3 gap-4">
            <x-ui.stat-card label="Total Revenue (GHS)" :value="number_format($income, 2)" />
            <x-ui.stat-card label="Total Expenses (GHS)" :value="number_format($expense, 2)" />
            <x-ui.stat-card label="Net Position (GHS)" :value="number_format($netPosition, 2)" />
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <x-ui.card>
                <h3 class="font-semibold text-slate-900 mb-4">By category</h3>
                @forelse ($byCategory as $row)
                    <div class="flex justify-between py-2 border-b border-slate-100 text-sm">
                        <span>{{ $row->category }} <x-ui.badge variant="{{ $row->type === 'income' ? 'success' : 'danger' }}" class="ml-1">{{ $row->type }}</x-ui.badge></span>
                        <span class="font-medium">{{ number_format($row->total, 2) }} GHS</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No transaction data yet.</p>
                @endforelse
            </x-ui.card>

            <x-ui.card>
                <h3 class="font-semibold text-slate-900 mb-4">Active budgets</h3>
                @forelse ($budgets as $budget)
                    <div class="py-2 border-b border-slate-100 text-sm">
                        <p class="font-medium">{{ $budget->name }}</p>
                        <p class="text-slate-500">{{ $budget->category }} · {{ number_format($budget->amount, 2) }} / {{ $budget->period }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No budgets defined.</p>
                @endforelse
                <div class="mt-4">
                    <x-ui.button :href="route('accounting.budgets.index')" variant="secondary" size="sm">Manage budgets</x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </div>

    <!-- Profit & Loss Tab Panel -->
    <div id="pnl-panel" role="tabpanel" class="hidden space-y-6">
        <x-ui.card>
            <div class="border-b border-[var(--border)] pb-3 mb-4 flex justify-between items-center">
                <h3 class="font-semibold text-[var(--text)] text-lg">Income Statement</h3>
                <span class="text-xs text-[var(--text-secondary)] uppercase font-semibold">Base Currency: GHS</span>
            </div>
            
            <div class="space-y-6">
                <!-- Revenue section -->
                <div>
                    <h4 class="font-bold text-[var(--text)] text-sm uppercase tracking-wider mb-2 border-b border-[var(--border)] pb-1">Revenue</h4>
                    @forelse ($profitAndLoss['revenues'] as $rev)
                        <div class="flex justify-between py-2 border-b border-[var(--border)] text-sm pl-4">
                            <span class="text-[var(--text-muted)]">{{ $rev['name'] }}</span>
                            <span class="font-medium text-[var(--text)]">{{ number_format($rev['amount'], 2) }}</span>
                        </div>
                    @empty
                        <div class="text-sm text-[var(--text-secondary)] py-2 pl-4">No revenue items recorded.</div>
                    @endforelse
                    <div class="flex justify-between py-2.5 font-bold text-sm bg-[var(--bg2)] border-t border-[var(--border)] mt-2 px-2">
                        <span>Total Revenue</span>
                        <span>{{ number_format($profitAndLoss['total_revenue'], 2) }}</span>
                    </div>
                </div>

                <!-- Expenses section -->
                <div>
                    <h4 class="font-bold text-[var(--text)] text-sm uppercase tracking-wider mb-2 border-b border-[var(--border)] pb-1">Operating Expenses</h4>
                    @forelse ($profitAndLoss['expenses'] as $exp)
                        <div class="flex justify-between py-2 border-b border-[var(--border)] text-sm pl-4">
                            <span class="text-[var(--text-muted)]">{{ $exp['name'] }}</span>
                            <span class="font-medium text-[var(--text)]">({{ number_format($exp['amount'], 2) }})</span>
                        </div>
                    @empty
                        <div class="text-sm text-[var(--text-secondary)] py-2 pl-4">No expense items recorded.</div>
                    @endforelse
                    <div class="flex justify-between py-2.5 font-bold text-sm bg-[var(--bg2)] border-t border-[var(--border)] mt-2 px-2">
                        <span>Total Operating Expenses</span>
                        <span>({{ number_format($profitAndLoss['total_expense'], 2) }})</span>
                    </div>
                </div>

                <!-- Net Income -->
                <div class="flex justify-between py-3 border-y-2 border-[var(--border)] font-extrabold text-base bg-[var(--bg3)]/40 px-2 mt-4 text-[var(--pur)]">
                    <span>Net Profit / (Loss)</span>
                    <span>{{ number_format($profitAndLoss['net_income'], 2) }}</span>
                </div>
            </div>
        </x-ui.card>
    </div>

    <!-- Balance Sheet Tab Panel -->
    <div id="balancesheet-panel" role="tabpanel" class="hidden space-y-6">
        <x-ui.card>
            <div class="border-b border-[var(--border)] pb-3 mb-4 flex justify-between items-center">
                <h3 class="font-semibold text-[var(--text)] text-lg">Statement of Financial Position</h3>
                <span class="text-xs text-[var(--text-secondary)] uppercase font-semibold">Base Currency: GHS</span>
            </div>

            <div class="space-y-6">
                <!-- Assets -->
                <div>
                    <h4 class="font-bold text-[var(--text)] text-sm uppercase tracking-wider mb-2 border-b border-[var(--border)] pb-1">Assets</h4>
                    @forelse ($balanceSheet['assets'] as $asset)
                        <div class="flex justify-between py-2 border-b border-[var(--border)] text-sm pl-4">
                            <span class="text-[var(--text-muted)]">{{ $asset['name'] }}</span>
                            <span class="font-medium text-[var(--text)]">{{ number_format($asset['amount'], 2) }}</span>
                        </div>
                    @empty
                        <div class="text-sm text-[var(--text-secondary)] py-2 pl-4">No assets recorded.</div>
                    @endforelse
                    <div class="flex justify-between py-2.5 font-bold text-sm bg-[var(--bg2)] border-t border-[var(--border)] mt-2 px-2">
                        <span>Total Assets</span>
                        <span>{{ number_format($balanceSheet['total_assets'], 2) }}</span>
                    </div>
                </div>

                <!-- Liabilities -->
                <div>
                    <h4 class="font-bold text-[var(--text)] text-sm uppercase tracking-wider mb-2 border-b border-[var(--border)] pb-1">Liabilities</h4>
                    @forelse ($balanceSheet['liabilities'] as $liab)
                        <div class="flex justify-between py-2 border-b border-[var(--border)] text-sm pl-4">
                            <span class="text-[var(--text-muted)]">{{ $liab['name'] }}</span>
                            <span class="font-medium text-[var(--text)]">{{ number_format($liab['amount'], 2) }}</span>
                        </div>
                    @empty
                        <div class="text-sm text-[var(--text-secondary)] py-2 pl-4">No liabilities recorded.</div>
                    @endforelse
                    <div class="flex justify-between py-2.5 font-bold text-sm bg-[var(--bg2)] border-t border-[var(--border)] mt-2 px-2">
                        <span>Total Liabilities</span>
                        <span>{{ number_format($balanceSheet['total_liabilities'], 2) }}</span>
                    </div>
                </div>

                <!-- Equity -->
                <div>
                    <h4 class="font-bold text-[var(--text)] text-sm uppercase tracking-wider mb-2 border-b border-[var(--border)] pb-1">Equity</h4>
                    @forelse ($balanceSheet['equities'] as $eq)
                        <div class="flex justify-between py-2 border-b border-[var(--border)] text-sm pl-4">
                            <span class="text-[var(--text-muted)]">{{ $eq['name'] }}</span>
                            <span class="font-medium text-[var(--text)]">{{ number_format($eq['amount'], 2) }}</span>
                        </div>
                    @empty
                        <div class="text-sm text-[var(--text-secondary)] py-2 pl-4">No equity recorded.</div>
                    @endforelse
                    <div class="flex justify-between py-2.5 font-bold text-sm bg-[var(--bg2)] border-t border-[var(--border)] mt-2 px-2">
                        <span>Total Equity</span>
                        <span>{{ number_format($balanceSheet['total_equity'], 2) }}</span>
                    </div>
                </div>

                <!-- Total Liabilities and Equity -->
                <div class="flex justify-between py-3 border-y-2 border-[var(--text-secondary)] font-extrabold text-base bg-[var(--bg3)] px-2 mt-4">
                    <span>Total Liabilities & Equity</span>
                    <span>{{ number_format($balanceSheet['total_liabilities_and_equity'], 2) }}</span>
                </div>
            </div>
        </x-ui.card>
    </div>

    <!-- Trial Balance Tab Panel -->
    <div id="trialbalance-panel" role="tabpanel" class="hidden space-y-6">
        <x-ui.table-shell>
            <thead>
                <tr>
                    <th class="py-2 font-medium">Account Code</th>
                    <th class="py-2 font-medium">Account Name</th>
                    <th class="py-2 font-medium text-right">Debit (GHS)</th>
                    <th class="py-2 font-medium text-right">Credit (GHS)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($trialBalance['rows'] as $row)
                    <tr class="border-b border-slate-100 text-sm">
                        <td class="py-3 font-semibold text-slate-600">{{ $row['code'] }}</td>
                        <td class="py-3 text-slate-800">{{ $row['name'] }} ({{ ucfirst($row['type']) }})</td>
                        <td class="py-3 text-right font-medium text-slate-900">
                            {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '-' }}
                        </td>
                        <td class="py-3 text-right font-medium text-slate-900">
                            {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-slate-500 text-sm">No ledger entries generated yet. Record a transaction to post entries.</td>
                    </tr>
                @endforelse
                <tr class="bg-slate-50 font-bold border-t border-slate-300">
                    <td colspan="2" class="py-3 text-slate-900 text-sm">Total Balanced Amount</td>
                    <td class="py-3 text-right text-slate-950 text-sm">
                        {{ number_format($trialBalance['total_debit'], 2) }}
                    </td>
                    <td class="py-3 text-right text-slate-950 text-sm">
                        {{ number_format($trialBalance['total_credit'], 2) }}
                    </td>
                </tr>
            </tbody>
        </x-ui.table-shell>
    </div>

    <!-- Client-side Tab Switcher Script -->
    <script>
        function switchTab(tabId) {
            // Hide all tab panels
            document.querySelectorAll('[role="tabpanel"]').forEach(el => el.classList.add('hidden'));
            
            // Inactivate all tab buttons
            document.querySelectorAll('[role="tab"]').forEach(el => {
                el.classList.remove('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400');
                el.classList.add('border-transparent', 'text-[var(--text-secondary)]');
            });
            
            // Show target panel
            document.getElementById(tabId + '-panel').classList.remove('hidden');
            
            // Activate target tab button
            document.getElementById(tabId + '-tab').classList.add('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400');
            document.getElementById(tabId + '-tab').classList.remove('border-transparent', 'text-[var(--text-secondary)]');
        }
    </script>
</x-ui.page>
