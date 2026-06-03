<x-ui.page title="Upload Media" maxWidth="3xl">
    <x-ui.form-card title="File details">
        <form method="POST" action="{{ route('distribution.media.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input id="title" class="mt-1" name="title" :value="old('title')" required />
            </div>
            <div>
                <x-input-label for="file" value="File" />
                <input id="file" type="file" name="file" required class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-semibold hover:file:bg-indigo-100" />
                <x-input-error :messages="$errors->get('file')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="auth-input mt-1">
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected(old('status', 'ready') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="notes" value="Notes" />
                <textarea id="notes" name="notes" rows="2" class="auth-input mt-1 resize-none">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-3">
                <x-primary-button>Upload</x-primary-button>
                <x-ui.button :href="route('distribution.media.index')" variant="ghost" size="sm">Cancel</x-ui.button>
            </div>
        </form>
    </x-ui.form-card>
</x-ui.page>
