<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">AI Training</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="grid md:grid-cols-2 gap-6">
            {{-- Training Data Stats --}}
            <x-ui.card title="Training Data">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted">Total Messages</span><span>{{ $stats['total_messages'] }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">User Messages</span><span>{{ $stats['user_messages'] }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">AI Responses</span><span>{{ $stats['assistant_messages'] }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Users with Conversations</span><span>{{ $stats['unique_users'] }}</span></div>
                    @if($trainingFileExists)
                        <div class="flex justify-between"><span class="text-muted">Exported File</span><span>{{ number_format($trainingFileSize / 1024, 1) }} KB</span></div>
                    @endif
                    <form action="{{ route('superadmin.training.export') }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-500">
                            Export Training Data
                        </button>
                    </form>
                </div>
            </x-ui.card>

            {{-- Local Model Status --}}
            <x-ui.card title="Local Model">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted">Endpoint</span>
                        <span>{{ $localEndpoint }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted">Model</span>
                        <span>{{ $localModel }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted">Status</span>
                        <span class="{{ $localModelAvailable ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ $localModelAvailable ? 'Online' : 'Unreachable' }}
                        </span>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <x-ui.card title="Fine-Tuning Guide">
            <div class="prose prose-invert text-sm max-w-none text-muted">
                <ol class="list-decimal list-inside space-y-1">
                    <li>Export training data using the button above (or run <code class="text-purple-300">php artisan ai:export-training-data --include-examples</code>)</li>
                    <li>Install <a href="https://github.com/unslothai/unsloth" class="text-purple-400 hover:text-purple-300">Unsloth</a> on a machine with 12GB+ VRAM</li>
                    <li>Follow <code class="text-purple-300">FINE_TUNE.md</code> to fine-tune on the exported data</li>
                    <li>Push the model to Ollama and set <code class="text-purple-300">AI_PROVIDER=local</code> in .env</li>
                </ol>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
