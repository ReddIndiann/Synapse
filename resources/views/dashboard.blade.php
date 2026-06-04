<x-ui.page title="Dashboard" description="Your Synapse workspace at a glance.">
    <!-- Stat Cards Summary Row -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        <x-ui.stat-card label="Open Tasks" :value="$openTasks" hint="Intelligent active checklist" />
        <x-ui.stat-card label="Net Balance" :value="number_format($income - $expense, 2).' GHS'" hint="Double-entry ledger sum" />
        <x-ui.stat-card label="Media Assets" :value="$mediaCount" hint="Ready for platform distribution" />
    </div>

    <!-- Core Workspaces Row -->
    <div class="grid lg:grid-cols-3 gap-5 mb-8">
        <x-ui.card class="relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-[var(--pur)]/5 rounded-full blur-xl group-hover:bg-[var(--pur)]/10 transition-all"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-[var(--pur)]/10 text-[var(--pur)] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <h3 class="font-bold text-[var(--text)] text-base">AI Assistant</h3>
            </div>
            <p class="text-xs text-[var(--text-muted)] leading-relaxed mb-5">Command processing, smart timing, and conflict resolution workspace.</p>
            <div class="flex items-center gap-2">
                <x-ui.button :href="route('assistant.chat')" variant="primary" size="sm" class="shadow-sm">Open Chat</x-ui.button>
                <x-ui.button :href="route('assistant.tasks.index')" variant="secondary" size="sm">Tasks</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card class="relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-full blur-xl group-hover:bg-emerald-500/10 transition-all"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="font-bold text-[var(--text)] text-base">Accountant</h3>
            </div>
            <p class="text-xs text-[var(--text-muted)] leading-relaxed mb-5">Double-entry general ledger, IFRS compliance, and reporting metrics.</p>
            <div class="flex items-center gap-2">
                <x-ui.button :href="route('accounting.transactions.index')" variant="primary" size="sm" class="shadow-sm">Transactions</x-ui.button>
                <x-ui.button :href="route('accounting.reports.index')" variant="secondary" size="sm">Reports</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card class="relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-sky-500/5 rounded-full blur-xl group-hover:bg-sky-500/10 transition-all"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="font-bold text-[var(--text)] text-base">Project Distributor</h3>
            </div>
            <p class="text-xs text-[var(--text-muted)] leading-relaxed mb-5">Queue uploads, transcode audio/video, and distribute to channels.</p>
            <div class="flex items-center gap-2">
                <x-ui.button :href="route('distribution.media.index')" variant="primary" size="sm" class="shadow-sm">Media Library</x-ui.button>
                <x-ui.button :href="route('distribution.publish.index')" variant="secondary" size="sm">Publish Queue</x-ui.button>
            </div>
        </x-ui.card>
    </div>

    <!-- Analytics & Live assistant Row -->
    <div class="grid lg:grid-cols-2 gap-6 mb-8">
        <!-- Cash Flow Trend Chart -->
        <x-ui.card class="relative overflow-hidden">
            <div class="flex items-center justify-between border-b border-[var(--border)] pb-3 mb-4">
                <h3 class="font-bold text-[var(--text)] text-sm tracking-tight uppercase">Cash Flow Trend · Last 30 days</h3>
                <span class="text-[10px] font-semibold text-[var(--text-secondary)] uppercase">Income vs Expense</span>
            </div>
            <div id="cash-flow-chart" class="min-h-[160px] w-full"></div>
        </x-ui.card>

        <!-- Live assistant feed preview -->
        <x-ui.card class="relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between border-b border-[var(--border)] pb-3 mb-4">
                <h3 class="font-bold text-[var(--text)] text-sm tracking-tight uppercase flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-[var(--pur)] shadow-[0_0_8px_var(--pur)] animate-pulse"></span>
                    Live AI Feed
                </h3>
                <x-ui.badge variant="success" class="!py-0.5 !px-2.5 !text-[9px] font-bold">MONITORING</x-ui.badge>
            </div>
            <div class="flex-1 flex flex-col gap-2.5 justify-end">
                <div class="cbbl cbus">Review recent budget status and warn of conflicts.</div>
                <div class="cbbl cbai"><strong>Assistant:</strong> Net Balance is positive. 2 high-priority tasks are active. No scheduling conflicts found.</div>
                <div class="flex gap-1.5 px-3 py-1">
                    <div class="tdot"></div>
                    <div class="tdot" style="animation-delay:.15s"></div>
                    <div class="tdot" style="animation-delay:.3s"></div>
                </div>
            </div>
        </x-ui.card>
    </div>

    <!-- Financial Budgets & Publishing Mix Row -->
    <div class="grid lg:grid-cols-2 gap-6 mb-8">
        <!-- Budget Tracking Card -->
        <x-ui.card class="relative overflow-hidden">
            <div class="flex items-center justify-between border-b border-[var(--border)] pb-3 mb-4">
                <h3 class="font-bold text-[var(--text)] text-sm tracking-tight uppercase">Monthly Budgets Usage</h3>
                <span class="text-[10px] font-semibold text-[var(--text-secondary)] uppercase">Spent vs Limit</span>
            </div>
            @if(count($budgetChartData['categories']) > 0)
                <div id="budget-chart" class="min-h-[220px] w-full"></div>
            @else
                <div class="py-16 text-center">
                    <div class="w-12 h-12 rounded-full bg-[var(--bg2)]/80 flex items-center justify-center mx-auto mb-3 text-[var(--text-secondary)]">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                    </div>
                    <p class="text-xs text-[var(--text-secondary)] font-medium">No budgets configured for this period.</p>
                </div>
            @endif
        </x-ui.card>

        <!-- Publication Channels Mix Card -->
        <x-ui.card class="relative overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-[var(--border)] pb-3 mb-4">
                    <h3 class="font-bold text-[var(--text)] text-sm tracking-tight uppercase">Publish Distribution Mix</h3>
                    <span class="text-[10px] font-semibold text-[var(--text-secondary)] uppercase">Queue Volume</span>
                </div>
                @if(count($publicationMixData['series']) > 0)
                    <div id="publish-mix-chart" class="min-h-[220px] w-full flex items-center justify-center py-2"></div>
                @else
                    <div class="py-16 text-center">
                        <div class="w-12 h-12 rounded-full bg-[var(--bg2)]/80 flex items-center justify-center mx-auto mb-3 text-[var(--text-secondary)]">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                        </div>
                        <p class="text-xs text-[var(--text-secondary)] font-medium">No publish jobs processed yet.</p>
                    </div>
                @endif
            </div>
        </x-ui.card>
    </div>

    <!-- Active Feeds Row -->
    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Recent Tasks Feed -->
        <x-ui.card>
            <div class="flex items-center justify-between border-b border-[var(--border)] pb-3 mb-4">
                <h3 class="font-bold text-[var(--text)] text-sm tracking-tight uppercase">Recent Tasks</h3>
                <x-ui.button :href="route('assistant.tasks.index')" variant="link" size="sm" class="!text-xs">View all</x-ui.button>
            </div>
            <div class="space-y-2.5 max-h-[300px] overflow-y-auto pr-1">
                @forelse ($recentTasks as $task)
                    <div class="flex items-center justify-between p-3 rounded-2xl border border-[var(--border)] bg-[var(--bg3)]/20 hover:bg-[var(--bg3)]/50 transition-all">
                        <div class="flex items-center gap-3">
                            <!-- Visual Checkbox indicator -->
                            <div class="w-4.5 h-4.5 rounded-md border border-[var(--border)] flex items-center justify-center shrink-0 text-white bg-white/40
                                @if($task->status === 'completed') !bg-[var(--pur)] !border-[var(--pur)] @endif">
                                @if($task->status === 'completed')
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </div>
                            <span class="text-xs font-semibold text-[var(--text)] truncate max-w-[200px] sm:max-w-xs @if($task->status === 'completed') line-through opacity-50 @endif">
                                {{ $task->title }}
                            </span>
                        </div>
                        <x-ui.badge variant="{{ $task->status === 'completed' ? 'success' : ($task->priority === 'high' ? 'danger' : 'primary') }}" class="!py-0.5 !px-2.5 !text-[10px] font-bold">
                            {{ $task->status === 'completed' ? 'completed' : $task->priority }}
                        </x-ui.badge>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-[var(--text-muted)] font-medium">No tasks captured yet.</div>
                @endforelse
            </div>
        </x-ui.card>

        <!-- Recent Transactions Feed -->
        <x-ui.card>
            <div class="flex items-center justify-between border-b border-[var(--border)] pb-3 mb-4">
                <h3 class="font-bold text-[var(--text)] text-sm tracking-tight uppercase">Recent Transactions</h3>
                <x-ui.button :href="route('accounting.transactions.index')" variant="link" size="sm" class="!text-xs">View all</x-ui.button>
            </div>
            <div class="space-y-2.5 max-h-[300px] overflow-y-auto pr-1">
                @forelse ($recentTransactions as $tx)
                    <div class="flex items-center justify-between p-3 rounded-2xl border border-[var(--border)] bg-[var(--bg3)]/20 hover:bg-[var(--bg3)]/50 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 font-bold text-xs
                                @if($tx->type === 'income') bg-emerald-500/10 text-emerald-400 @else bg-rose-500/10 text-rose-400 @endif">
                                {{ $tx->type === 'income' ? 'IN' : 'OUT' }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-[var(--text)] truncate max-w-[150px] sm:max-w-xs">{{ $tx->category }}</p>
                                <p class="text-[10px] text-[var(--text-muted)] font-medium">{{ $tx->occurred_at->format('M d, Y') }} · {{ $tx->payment_method }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold tracking-tight shrink-0
                            @if($tx->type === 'income') text-emerald-400 @else text-rose-400 @endif">
                            {{ $tx->type === 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2) }} {{ $tx->currency }}
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-[var(--text-muted)] font-medium">No transactions recorded yet.</div>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <!-- ApexCharts Library & Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Theme Mode Helper
            const getThemeMode = () => document.documentElement.classList.contains('dark') ? 'dark' : 'light';

            // 1. CASH FLOW CHART (Area)
            const cashFlowData = @json($cashFlowData);
            const cashFlowOptions = {
                series: [
                    { name: 'Income', data: cashFlowData.income },
                    { name: 'Expense', data: cashFlowData.expense }
                ],
                chart: {
                    type: 'area',
                    height: 160,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    background: 'transparent',
                    foreColor: 'var(--text-secondary)',
                    fontFamily: 'Space Grotesk, sans-serif'
                },
                colors: ['#10b981', '#ef4444'], // Emerald (Income), Rose (Expense)
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                grid: {
                    borderColor: 'var(--border)',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: false } },
                    padding: { top: 0, right: 10, bottom: 0, left: 10 }
                },
                xaxis: {
                    categories: cashFlowData.categories,
                    labels: { show: true, style: { fontSize: '9px', fontWeight: 600 } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: { show: true, style: { fontSize: '9px', fontWeight: 600 } }
                },
                theme: { mode: getThemeMode() },
                tooltip: {
                    theme: getThemeMode(),
                    x: { format: 'dd MMM' }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.25,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '10px',
                    fontWeight: 600,
                    labels: { colors: 'var(--text)' }
                }
            };
            const cashFlowChart = new ApexCharts(document.querySelector("#cash-flow-chart"), cashFlowOptions);
            cashFlowChart.render();

            // 2. BUDGET COMPARISON CHART (Bar)
            let budgetChart = null;
            const budgetChartData = @json($budgetChartData);
            if (budgetChartData.categories && budgetChartData.categories.length > 0) {
                const budgetOptions = {
                    series: [
                        { name: 'Spent', data: budgetChartData.actuals },
                        { name: 'Limit', data: budgetChartData.limits }
                    ],
                    chart: {
                        type: 'bar',
                        height: 220,
                        toolbar: { show: false },
                        background: 'transparent',
                        foreColor: 'var(--text-secondary)',
                        fontFamily: 'Space Grotesk, sans-serif'
                    },
                    colors: ['#8b5cf6', '#4b5563'], // Violet (Spent), Slate Grey (Limit)
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            dataLabels: { position: 'top' },
                            barHeight: '60%',
                            borderRadius: 4
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) {
                            return val.toLocaleString() + ' GHS';
                        },
                        style: { fontSize: '9px', colors: ['var(--text)'] }
                    },
                    stroke: { show: true, width: 1, colors: ['transparent'] },
                    grid: {
                        borderColor: 'var(--border)',
                        strokeDashArray: 4
                    },
                    xaxis: {
                        categories: budgetChartData.categories,
                        labels: { show: true, style: { fontSize: '9px', fontWeight: 600 } }
                    },
                    yaxis: {
                        labels: { show: true, style: { fontSize: '10px', fontWeight: 600 } }
                    },
                    theme: { mode: getThemeMode() },
                    tooltip: {
                        theme: getThemeMode(),
                        y: {
                            formatter: function (val) {
                                return val.toLocaleString() + ' GHS';
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        fontSize: '10px',
                        fontWeight: 600,
                        labels: { colors: 'var(--text)' }
                    }
                };
                budgetChart = new ApexCharts(document.querySelector("#budget-chart"), budgetOptions);
                budgetChart.render();
            }

            // 3. PUBLISH QUEUE MIX (Donut)
            let publishMixChart = null;
            const publicationMixData = @json($publicationMixData);
            if (publicationMixData.series && publicationMixData.series.length > 0) {
                const publishMixOptions = {
                    series: publicationMixData.series,
                    labels: publicationMixData.labels,
                    chart: {
                        type: 'donut',
                        height: 220,
                        background: 'transparent',
                        foreColor: 'var(--text-secondary)',
                        fontFamily: 'Space Grotesk, sans-serif'
                    },
                    colors: ['#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'], // Sky, Emerald, Amber, Violet, Pink
                    dataLabels: { enabled: false },
                    legend: {
                        position: 'bottom',
                        fontSize: '10px',
                        fontWeight: 600,
                        labels: { colors: 'var(--text)' }
                    },
                    stroke: { colors: ['var(--surface)'] },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                labels: {
                                    show: true,
                                    name: { show: true, fontSize: '12px', fontWeight: 600, color: 'var(--text)' },
                                    value: { show: true, fontSize: '14px', fontWeight: 700, color: 'var(--text)', formatter: (v) => v },
                                    total: {
                                        show: true,
                                        label: 'Total Jobs',
                                        fontSize: '10px',
                                        fontWeight: 600,
                                        color: 'var(--text-secondary)',
                                        formatter: function (w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    theme: { mode: getThemeMode() },
                    tooltip: {
                        theme: getThemeMode()
                    }
                };
                publishMixChart = new ApexCharts(document.querySelector("#publish-mix-chart"), publishMixOptions);
                publishMixChart.render();
            }

            // Listen to Theme Toggles dynamically
            window.addEventListener('theme-changed', () => {
                const isDark = document.documentElement.classList.contains('dark');
                const themeMode = isDark ? 'dark' : 'light';
                
                cashFlowChart.updateOptions({
                    theme: { mode: themeMode },
                    tooltip: { theme: themeMode }
                });

                if (budgetChart) {
                    budgetChart.updateOptions({
                        theme: { mode: themeMode },
                        tooltip: { theme: themeMode }
                    });
                }

                if (publishMixChart) {
                    publishMixChart.updateOptions({
                        theme: { mode: themeMode },
                        tooltip: { theme: themeMode }
                    });
                }
            });
        });
    </script>
</x-ui.page>
