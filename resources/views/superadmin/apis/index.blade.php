<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">API Management</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="grid md:grid-cols-3 gap-6">
            {{-- Gemini --}}
            <x-ui.card title="Google Gemini">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted">Status</span>
                        <span class="{{ $providers['gemini']['configured'] ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $providers['gemini']['configured'] ? 'Configured' : 'Not Configured' }}
                        </span>
                    </div>
                    @if($providers['gemini']['configured'])
                        <div class="flex justify-between"><span class="text-muted">Model</span><span>{{ $providers['gemini']['model'] }}</span></div>
                        <div class="flex justify-between"><span class="text-muted">Key</span><span>{{ $providers['gemini']['key_preview'] }}</span></div>
                    @endif
                    <button onclick="testProvider('gemini')" class="mt-3 px-3 py-1.5 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-500" {{ $providers['gemini']['configured'] ? '' : 'disabled' }}>
                        Test Connection
                    </button>
                </div>
            </x-ui.card>

            {{-- Local AI --}}
            <x-ui.card title="Local AI (Ollama)">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted">Endpoint</span><span>{{ $providers['local']['endpoint'] }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Model</span><span>{{ $providers['local']['model'] }}</span></div>
                    <button onclick="testProvider('local')" class="mt-3 px-3 py-1.5 rounded-lg bg-cyan-600 text-white text-sm hover:bg-cyan-500">
                        Test Connection
                    </button>
                </div>
            </x-ui.card>

            {{-- Regex Fallback --}}
            <x-ui.card title="Regex Parser">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted">Status</span><span class="text-emerald-400">Always Available</span></div>
                    <p class="text-muted mt-1">{{ $providers['regex']['description'] }}</p>
                </div>
            </x-ui.card>
        </div>

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
