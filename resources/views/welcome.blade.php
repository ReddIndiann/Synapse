<x-ui.marketing-layout>
    <!-- HERO SECTION -->
    <section class="relative py-16 lg:py-24 overflow-hidden">
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--pur)]/15 border border-[var(--pur)]/30 text-xs font-bold text-[#a78bfa] mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--pur)] shadow-[0_0_6px_var(--pur)] animate-pulse"></span>
                    Fully Automated Personal Assistant & Accountant
                </div>

                <!-- Main Title -->
                <h1 class="text-4xl sm:text-6xl font-bold tracking-tight leading-[1.08] mb-6 text-[var(--text)]">
                    Run Your Operations on <br><span class="gt">Autopilot with Synapse</span>
                </h1>

                <!-- Subtitle -->
                <p class="text-base sm:text-lg text-[var(--text-muted)] max-w-2xl mx-auto mb-8 font-medium">
                    Synapse leverages intelligent AI to auto-schedule tasks from natural language, enforce double-entry accounting compliance, and publish digital media to external platforms automatically.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-wrap justify-center gap-3">
                    @auth
                        <x-ui.button :href="route('dashboard')" variant="primary" class="!px-6 !py-3.5 !rounded-2xl">Open Dashboard</x-ui.button>
                    @else
                        <x-ui.button :href="route('register')" variant="primary" class="!px-6 !py-3.5 !rounded-2xl">Create Free Account</x-ui.button>
                        <x-ui.button :href="route('login')" variant="secondary" class="!px-6 !py-3.5 !rounded-2xl">Sign In</x-ui.button>
                    @endauth
                </div>

                <!-- Trusted Integrations -->
                <div class="mt-14">
                    <p class="text-[10px] font-bold text-[var(--text-secondary)] uppercase tracking-widest mb-4">Supported channels &amp; integrations</p>
                    <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-10 text-[var(--text-secondary)] font-bold opacity-60">
                        <span class="hover:opacity-100 transition-opacity cursor-pointer text-sm">YouTube</span>
                        <span class="hover:opacity-100 transition-opacity cursor-pointer text-sm">Spotify</span>
                        <span class="hover:opacity-100 transition-opacity cursor-pointer text-sm">Audiomack</span>
                        <span class="hover:opacity-100 transition-opacity cursor-pointer text-sm">Laravel Queues</span>
                        <span class="hover:opacity-100 transition-opacity cursor-pointer text-sm">IFRS Bookkeeping</span>
                    </div>
                </div>
            </div>

            <!-- Dashboard Preview Window -->
            <div class="mt-16 max-w-5xl mx-auto rounded-3xl overflow-hidden border border-[var(--border)] bg-[var(--surface)] shadow-2xl relative z-20 transition-all duration-300">
                <!-- Title Bar -->
                <div class="bg-[var(--bg3)] px-5 py-3.5 border-b border-[var(--border)] flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#ff5f57]"></span>
                    <span class="w-3 h-3 rounded-full bg-[#ffbd2e]"></span>
                    <span class="w-3 h-3 rounded-full bg-[#28c840]"></span>
                    <span class="mx-auto text-[11px] font-semibold text-[var(--text-secondary)]">Synapse Workspace &bull; synapse.laravel/dashboard</span>
                </div>
                <!-- Mock grid -->
                <div class="grid md:grid-cols-[200px_1fr] min-h-[380px]">
                    <!-- Sidebar -->
                    <div class="bg-[var(--bg2)] border-r border-[var(--border)] p-4 space-y-4 hidden md:block">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-[var(--text-secondary)] opacity-75">Workspace Modules</div>
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2.5 px-3 py-2 rounded-xl bg-[var(--pur)]/15 border border-[var(--pur)]/20 text-[#a78bfa] text-xs font-semibold cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z" stroke-width="2"/></svg> 
                                Dashboard
                            </div>
                            <div class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-[var(--text-secondary)] text-xs font-semibold hover:bg-[var(--bg3)] cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M8 12h.01M12 12h.01M16 12h.01" stroke-width="2"/></svg> 
                                AI Chat Workspace
                            </div>
                            <div class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-[var(--text-secondary)] text-xs font-semibold hover:bg-[var(--bg3)] cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke-width="2"/></svg> 
                                Tasks List
                            </div>
                            <div class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-[var(--text-secondary)] text-xs font-semibold hover:bg-[var(--bg3)] cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" stroke-width="2"/></svg> 
                                Transactions
                            </div>
                            <div class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-[var(--text-secondary)] text-xs font-semibold hover:bg-[var(--bg3)] cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9" stroke-width="2"/></svg> 
                                Publish Queue
                            </div>
                        </div>
                    </div>
                    <!-- Workspace Content -->
                    <div class="p-6 flex flex-col justify-between">
                        <!-- Stats Pills Grid -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                            <div class="stpill">
                                <div class="text-xl font-bold gt">3 Active</div>
                                <div class="text-[9px] font-bold text-[var(--text-secondary)] uppercase">Open Tasks</div>
                            </div>
                            <div class="stpill">
                                <div class="text-xl font-bold gt">GHS 12.4K</div>
                                <div class="text-[9px] font-bold text-[var(--text-secondary)] uppercase">Net Balance</div>
                            </div>
                            <div class="stpill">
                                <div class="text-xl font-bold gt">18 Files</div>
                                <div class="text-[9px] font-bold text-[var(--text-secondary)] uppercase">Media Library</div>
                            </div>
                            <div class="stpill">
                                <div class="text-xl font-bold gt">4 Jobs</div>
                                <div class="text-[9px] font-bold text-[var(--text-secondary)] uppercase">Publish Queue</div>
                            </div>
                        </div>
                        <!-- Inner Grid -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <!-- Left Flex Chart -->
                            <div class="bg-[var(--bg3)] border border-[var(--border)] rounded-2xl p-4">
                                <div class="text-[10px] font-bold text-[var(--text-secondary)] uppercase mb-3 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[var(--pur)]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z" stroke-width="2"/></svg> 
                                    Activity &bull; Operations Count
                                </div>
                                <div class="flex items-end gap-2.5 h-[80px]">
                                    <div class="bbar rounded-t-sm" style="height: 45%"></div>
                                    <div class="bbar rounded-t-sm" style="height: 72%"></div>
                                    <div class="bbar rounded-t-sm" style="height: 55%"></div>
                                    <div class="bbar rounded-t-sm" style="height: 88%"></div>
                                    <div class="bbar rounded-t-sm" style="height: 63%"></div>
                                    <div class="bbar rounded-t-sm" style="height: 95%"></div>
                                    <div class="bbar rounded-t-sm" style="height: 78%"></div>
                                </div>
                            </div>
                            <!-- Right Live Chat Preview -->
                            <div class="bg-[var(--bg3)] border border-[var(--border)] rounded-2xl p-4 flex flex-col gap-2 justify-end min-h-[120px]">
                                <div class="text-[10px] font-bold text-[var(--text-secondary)] uppercase mb-1 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--pur)] shadow-[0_0_6px_var(--pur)] animate-pulse"></span> Live Assistant
                                </div>
                                <div class="cbbl cbus !text-[9px] !py-1.5 !px-2.5">Spent 150 GHS on Internet Utilities</div>
                                <div class="cbbl cbai !text-[9px] !py-1.5 !px-2.5"><strong>Ledger Updated:</strong> GHS 150.00 debited to Utilities Expense. Conflict checks completed.</div>
                                <div class="flex gap-1.5 px-2 py-0.5">
                                    <div class="tdot !w-1 !h-1"></div>
                                    <div class="tdot !w-1 !h-1" style="animation-delay:.15s"></div>
                                    <div class="tdot !w-1 !h-1" style="animation-delay:.3s"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SOCIAL PROOF SECTION -->
    <section class="py-12 bg-[var(--bg2)] border-y border-[var(--border)]">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div>
                    <div class="pnum text-3xl font-extrabold gt">Double-Entry</div>
                    <div class="text-xs font-semibold text-[var(--text-secondary)] mt-1.5">IFRS Compliance</div>
                </div>
                <div>
                    <div class="pnum text-3xl font-extrabold gt">NLP Engine</div>
                    <div class="text-xs font-semibold text-[var(--text-secondary)] mt-1.5">Natural Task Scheduling</div>
                </div>
                <div>
                    <div class="pnum text-3xl font-extrabold gt">Multi-Platform</div>
                    <div class="text-xs font-semibold text-[var(--text-secondary)] mt-1.5">Content Distributor</div>
                </div>
                <div>
                    <div class="pnum text-3xl font-extrabold gt">Active Queue</div>
                    <div class="text-xs font-semibold text-[var(--text-secondary)] mt-1.5">Background Processing</div>
                </div>
            </div>
        </div>
    </section>

    <!-- THE PROBLEM SECTION -->
    <section class="py-20 relative">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <span class="slbl">The Challenges</span>
                <h2 class="stitle">Managing operations is <span class="gt">complex</span></h2>
                <p class="ssub mx-auto text-base text-[var(--text-muted)] font-medium mt-3">
                    Synapse is built to resolve the three primary bottlenecks holding back creator businesses and operations teams.
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <x-ui.card class="p-6 h-full flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center mb-5 shrink-0 text-red-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--text)] mb-3.5">Scheduling Conflicts</h3>
                        <p class="text-xs text-[var(--text-muted)] leading-relaxed font-medium">
                            Manual scheduling leads to overlapping tasks, missed deadlines, and disorganization. Synapse parses natural inputs to schedule tasks and flag timing conflicts automatically.
                        </p>
                    </div>
                </x-ui.card>
                <!-- Card 2 -->
                <x-ui.card class="p-6 h-full flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mb-5 shrink-0 text-amber-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--text)] mb-3.5">Accounting Errors</h3>
                        <p class="text-xs text-[var(--text-muted)] leading-relaxed font-medium">
                            Single-entry spreadsheets lead to discrepancies and missing reports. Synapse enforces double-entry rules, making sure every transaction matches credits and debits correctly.
                        </p>
                    </div>
                </x-ui.card>
                <!-- Card 3 -->
                <x-ui.card class="p-6 h-full flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center mb-5 shrink-0 text-[#a78bfa]">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--text)] mb-3.5">Repetitive Uploads</h3>
                        <p class="text-xs text-[var(--text-muted)] leading-relaxed font-medium">
                            Uploading and filling metadata on YouTube, Spotify, and Audiomack manually is tedious. Synapse handles uploads via a queue worker with metadata extraction.
                        </p>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="py-20 bg-[var(--bg2)]">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <span class="slbl">Core Capabilities</span>
                <h2 class="stitle">Everything you need to <span class="gt">run your business</span></h2>
                <p class="ssub mx-auto text-base text-[var(--text-muted)] font-medium mt-3">
                    Synapse combines three powerful modules into a single seamless application.
                </p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Feature 1 -->
                <x-ui.card class="p-6 h-full flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-2xl bg-[var(--pur)]/10 border border-[var(--pur)]/20 flex items-center justify-center mb-4 text-[var(--pur)]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--text)] mb-2.5">NLP Assistant</h3>
                        <p class="text-xs text-[var(--text-muted)] leading-relaxed font-medium mb-4">
                            Type naturally to interact with the assistant. Our NLP parsing engine extracts scheduling actions and logs financial transactions automatically.
                        </p>
                    </div>
                    <span class="inline-flex self-start text-[10px] font-bold uppercase tracking-wider text-[var(--pur)] px-2.5 py-1 rounded-full bg-[var(--pur)]/10 border border-[var(--pur)]/15">NLP Engine</span>
                </x-ui.card>
                <!-- Feature 2 -->
                <x-ui.card class="p-6 h-full flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-2xl bg-[var(--pur)]/10 border border-[var(--pur)]/20 flex items-center justify-center mb-4 text-[var(--pur)]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--text)] mb-2.5">Compliant Accounting</h3>
                        <p class="text-xs text-[var(--text-muted)] leading-relaxed font-medium mb-4">
                            Run IFRS-compliant bookkeeping with standard ledger accounts, journals, and automatically balanced Trial Balance, P&L, and Balance Sheet reports.
                        </p>
                    </div>
                    <span class="inline-flex self-start text-[10px] font-bold uppercase tracking-wider text-[var(--pur)] px-2.5 py-1 rounded-full bg-[var(--pur)]/10 border border-[var(--pur)]/15">IFRS Compliant</span>
                </x-ui.card>
                <!-- Feature 3 -->
                <x-ui.card class="p-6 h-full flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-2xl bg-[var(--pur)]/10 border border-[var(--pur)]/20 flex items-center justify-center mb-4 text-[var(--pur)]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--text)] mb-2.5">Project Distributor</h3>
                        <p class="text-xs text-[var(--text-muted)] leading-relaxed font-medium mb-4">
                            Publish media files automatically. Synapse handles transcoding, metadata validation, and deploys asynchronously via background queue workers.
                        </p>
                    </div>
                    <span class="inline-flex self-start text-[10px] font-bold uppercase tracking-wider text-[var(--pur)] px-2.5 py-1 rounded-full bg-[var(--pur)]/10 border border-[var(--pur)]/15">Queue Worker</span>
                </x-ui.card>
                <!-- Feature 4 -->
                <x-ui.card class="p-6 h-full flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-2xl bg-[var(--pur)]/10 border border-[var(--pur)]/20 flex items-center justify-center mb-4 text-[var(--pur)]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--text)] mb-2.5">Multi-Currency System</h3>
                        <p class="text-xs text-[var(--text-muted)] leading-relaxed font-medium mb-4">
                            Perform transactions in multiple currencies (GHS, USD, EUR, etc.). Synapse translates them dynamically to your base currency using active exchange rates.
                        </p>
                    </div>
                    <span class="inline-flex self-start text-[10px] font-bold uppercase tracking-wider text-[var(--pur)] px-2.5 py-1 rounded-full bg-[var(--pur)]/10 border border-[var(--pur)]/15">Exchange Rates</span>
                </x-ui.card>
                <!-- Feature 5 -->
                <x-ui.card class="p-6 h-full flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-2xl bg-[var(--pur)]/10 border border-[var(--pur)]/20 flex items-center justify-center mb-4 text-[var(--pur)]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--text)] mb-2.5">Conflict Resolution</h3>
                        <p class="text-xs text-[var(--text-muted)] leading-relaxed font-medium mb-4">
                            Synapse detects overlapping schedules and prompts conflict warnings inside the chat workspace with interactive, one-tap resolution buttons.
                        </p>
                    </div>
                    <span class="inline-flex self-start text-[10px] font-bold uppercase tracking-wider text-[var(--pur)] px-2.5 py-1 rounded-full bg-[var(--pur)]/10 border border-[var(--pur)]/15">Conflict Warns</span>
                </x-ui.card>
                <!-- Feature 6 -->
                <x-ui.card class="p-6 h-full flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-2xl bg-[var(--pur)]/10 border border-[var(--pur)]/20 flex items-center justify-center mb-4 text-[var(--pur)]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--text)] mb-2.5">Queue Monitor</h3>
                        <p class="text-xs text-[var(--text-muted)] leading-relaxed font-medium mb-4">
                            Monitor queue execution status in real-time. View logs, track processing chunks, and verify when assets are successfully deployed.
                        </p>
                    </div>
                    <span class="inline-flex self-start text-[10px] font-bold uppercase tracking-wider text-[var(--pur)] px-2.5 py-1 rounded-full bg-[var(--pur)]/10 border border-[var(--pur)]/15">Active Console</span>
                </x-ui.card>
            </div>
        </div>
    </section>
</x-ui.marketing-layout>
