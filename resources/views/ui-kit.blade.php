<x-ui.page title="UI Template" description="Reusable Synapse components — copy these patterns into new pages.">
    {{-- Buttons --}}
    <x-ui.card class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Buttons</h3>
        <div class="flex flex-wrap gap-3">
            <x-ui.button variant="primary">Primary</x-ui.button>
            <x-ui.button variant="secondary">Secondary</x-ui.button>
            <x-ui.button variant="danger">Danger</x-ui.button>
            <x-ui.button variant="ghost">Ghost</x-ui.button>
            <x-ui.button variant="link">Link</x-ui.button>
        </div>
    </x-ui.card>

    {{-- Badges --}}
    <x-ui.card class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Badges</h3>
        <div class="flex flex-wrap gap-2">
            <x-ui.badge>Default</x-ui.badge>
            <x-ui.badge variant="primary">Primary</x-ui.badge>
            <x-ui.badge variant="success">Success</x-ui.badge>
            <x-ui.badge variant="warning">Warning</x-ui.badge>
            <x-ui.badge variant="danger">Danger</x-ui.badge>
        </div>
    </x-ui.card>

    {{-- Alerts --}}
    <x-ui.card class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Alerts</h3>
        <div class="space-y-3">
            <x-ui.alert variant="info">Information message for the user.</x-ui.alert>
            <x-ui.alert variant="success">Action completed successfully.</x-ui.alert>
            <x-ui.alert variant="warning">Please review before continuing.</x-ui.alert>
            <x-ui.alert variant="danger">Something went wrong.</x-ui.alert>
        </div>
    </x-ui.card>

    {{-- Stats --}}
    <x-ui.card class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Stat Cards</h3>
        <div class="grid sm:grid-cols-3 gap-4">
            <x-ui.stat-card label="Revenue" value="$12,450" hint="+8% this month" />
            <x-ui.stat-card label="Tasks" value="24" hint="6 due today" />
            <x-ui.stat-card label="Users" value="18" hint="3 new this week" />
        </div>
    </x-ui.card>

    {{-- Form inputs --}}
    <x-ui.form-card title="Form inputs" description="Use with existing Breeze input components." class="mb-6">
        <div class="space-y-4 max-w-md">
            <div>
                <x-input-label for="demo_email" value="Email" />
                <x-text-input id="demo_email" class="mt-1" type="email" placeholder="you@example.com" />
            </div>
            <div>
                <x-input-label for="demo_password" value="Password" />
                <x-text-input id="demo_password" class="mt-1" type="password" />
            </div>
            <x-ui.button variant="primary">Submit</x-ui.button>
        </div>
    </x-ui.form-card>

    {{-- Table --}}
    <x-ui.table-shell>
        <thead>
            <tr>
                <th class="py-2 font-medium">Name</th>
                <th class="py-2 font-medium">Role</th>
                <th class="py-2 font-medium">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="py-3">Jane Doe</td>
                <td class="py-3"><x-ui.badge variant="primary">admin</x-ui.badge></td>
                <td class="py-3"><x-ui.badge variant="success">Active</x-ui.badge></td>
            </tr>
            <tr>
                <td class="py-3">John Smith</td>
                <td class="py-3"><x-ui.badge>staff</x-ui.badge></td>
                <td class="py-3"><x-ui.badge variant="success">Active</x-ui.badge></td>
            </tr>
        </tbody>
    </x-ui.table-shell>

    {{-- Usage reference --}}
    <x-ui.card class="mt-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-3">Quick reference</h3>
        <div class="text-sm text-slate-600 space-y-2 font-mono">
            <p>&lt;x-ui.page title="..."&gt; ... &lt;/x-ui.page&gt;</p>
            <p>&lt;x-ui.auth-layout title="..."&gt; ... &lt;/x-ui.auth-layout&gt;</p>
            <p>&lt;x-ui.marketing-layout&gt; ... &lt;/x-ui.marketing-layout&gt;</p>
            <p>&lt;x-ui.button variant="primary"&gt; ... &lt;/x-ui.button&gt;</p>
            <p>&lt;x-ui.table-shell&gt; ... &lt;/x-ui.table-shell&gt;</p>
        </div>
    </x-ui.card>
</x-ui.page>
