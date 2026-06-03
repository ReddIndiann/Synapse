<x-ui.page title="Edit Media" maxWidth="3xl">
    <x-ui.form-card title="Asset details">
        <form method="POST" action="{{ route('distribution.media.update', $asset) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input id="title" class="mt-1" name="title" :value="old('title', $asset->title)" required />
            </div>
            <p class="text-sm text-slate-500">File: {{ $asset->filename }} (re-upload not supported in this phase)</p>
            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="auth-input mt-1">
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected(old('status', $asset->status) === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="notes" value="Notes" />
                <textarea id="notes" name="notes" rows="2" class="auth-input mt-1 resize-none">{{ old('notes', $asset->notes) }}</textarea>
            </div>
            <div class="flex gap-3">
                <x-primary-button>Update</x-primary-button>
                <x-ui.button :href="route('distribution.media.index')" variant="ghost" size="sm">Cancel</x-ui.button>
            </div>
        </form>
    </x-ui.form-card>
</x-ui.page>
