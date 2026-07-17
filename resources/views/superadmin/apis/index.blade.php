<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">API Management</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <x-ui.card title="Active Provider">
            <div class="text-sm space-y-2">
                <div class="flex justify-between">
                    <span class="text-muted">AI_PROVIDER</span>
                    <span class="font-semibold text-purple-300">{{ $activeProvider }}</span>
                </div>
                @if(!empty($fallbackProviders))
                    <div class="flex justify-between">
                        <span class="text-muted">Fallback chain</span>
                        <span>{{ implode(' → ', $fallbackProviders) }} → regex</span>
                    </div>
                @else
                    <p class="text-muted text-xs">No fallback providers configured. Failed API calls fall back to the built-in regex parser.</p>
                @endif
            </div>
        </x-ui.card>

        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($providers as $name => $provider)
                <x-ui.card :title="$provider['label'] ?? ucfirst($name)">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted">Provider key</span>
                            <code class="text-xs text-purple-300">{{ $name }}</code>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">Status</span>
                            <span class="{{ ($provider['configured'] ?? false) ? 'text-emerald-400' : 'text-red-400' }}">
                                @if($name === 'regex')
                                    Always Available
                                @else
                                    {{ ($provider['configured'] ?? false) ? 'Configured' : 'Not Configured' }}
                                @endif
                            </span>
                        </div>

                        @if($name === 'regex')
                            <p class="text-muted mt-1">{{ $provider['description'] ?? 'Built-in PHP pattern matching.' }}</p>
                        @else
                            @if(!empty($provider['model']))
                                <div class="flex justify-between"><span class="text-muted">Model</span><span class="text-right truncate max-w-[180px]" title="{{ $provider['model'] }}">{{ $provider['model'] }}</span></div>
                            @endif
                            @if(!empty($provider['endpoint']))
                                <div class="flex justify-between"><span class="text-muted">Endpoint</span><span class="text-right truncate max-w-[180px]" title="{{ $provider['endpoint'] }}">{{ $provider['endpoint'] }}</span></div>
                            @endif
                            @if(!empty($provider['key_preview']))
                                <div class="flex justify-between"><span class="text-muted">Key</span><span>{{ $provider['key_preview'] }}</span></div>
                            @endif

                            <button
                                onclick="testProvider('{{ $name }}')"
                                class="mt-3 px-3 py-1.5 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-500 disabled:opacity-50"
                                {{ ($provider['configured'] ?? false) ? '' : 'disabled' }}
                            >
                                Test Connection
                            </button>
                        @endif

                        @if($activeProvider === $name)
                            <span class="inline-block mt-2 text-[10px] font-bold uppercase tracking-wider text-emerald-400">Active</span>
                        @endif
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <x-ui.alert variant="info">
            Set <code class="text-purple-300">AI_PROVIDER</code> in <code class="text-purple-300">.env</code> to any provider key above.
            Use <code class="text-purple-300">openai_compatible</code> for Azure OpenAI or any custom gateway that supports OpenAI chat completions.
            Optional: <code class="text-purple-300">AI_FALLBACK_PROVIDERS=openai,gemini,local</code>
        </x-ui.alert>

        <div id="test-result" class="hidden"></div>
    </div>

    @push('scripts')
    <script>
        async function testProvider(provider) {
            const btn = event.target;
            btn.disabled = true;
            btn.textContent = 'Testing...';

            const res = await fetch('{{ route("superadmin.apis.test", ["provider" => ""]) }}' + provider);
            const data = await res.json();

            const result = document.getElementById('test-result');
            result.className = 'p-4 rounded-lg ' + (data.status === 'ok' ? 'bg-emerald-500/10 text-emerald-300' : 'bg-red-500/10 text-red-300');
            result.textContent = data.message;
            result.classList.remove('hidden');

            btn.disabled = false;
            btn.textContent = 'Test Connection';
        }
    </script>
    @endpush
</x-app-layout>
