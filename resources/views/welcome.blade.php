<x-ui.marketing-layout>
    <section class="ui-container py-12 lg:py-20">
        <div class="grid lg:grid-cols-2 gap-8 items-stretch">
            <x-ui.card class="p-8 lg:p-10">
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600">AI-First Business Platform</p>
                <h1 class="mt-3 text-3xl lg:text-5xl font-bold tracking-tight text-slate-900">
                    Manage tasks, finance, and publishing from one place.
                </h1>
                <p class="mt-4 text-slate-600 leading-relaxed">
                    Synapse helps teams run daily operations with intelligent automation: schedule work, record transactions, and distribute content with less manual effort.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    @auth
                        <x-ui.button :href="route('dashboard')" variant="primary">Open Dashboard</x-ui.button>
                    @else
                        <x-ui.button :href="route('login')" variant="primary">Get Started</x-ui.button>
                        @if (Route::has('register'))
                            <x-ui.button :href="route('register')" variant="secondary">Create Account</x-ui.button>
                        @endif
                    @endauth
                </div>
            </x-ui.card>

            <x-ui.card class="p-8 lg:p-10">
                <h2 class="text-xl font-semibold text-slate-900">Core Modules</h2>
                <div class="mt-6 space-y-4">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="font-medium text-slate-800">AI Assistant</p>
                        <p class="text-sm text-slate-600 mt-1">Natural-language tasks, smart scheduling, and reminder automation.</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="font-medium text-slate-800">Accounting</p>
                        <p class="text-sm text-slate-600 mt-1">Double-entry financial tracking with budgets and reporting.</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="font-medium text-slate-800">Distribution</p>
                        <p class="text-sm text-slate-600 mt-1">Upload, process, and publish digital content from one workflow.</p>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </section>
</x-ui.marketing-layout>
