<x-ui.page title="Edit Media Asset" maxWidth="3xl">
    <x-ui.form-card title="Asset Details">
        <form method="POST" action="{{ route('distribution.media.update', $asset) }}" class="space-y-5">
            @csrf @method('PUT')
            
            <div>
                <x-input-label for="title" value="Asset Title" />
                <x-text-input id="title" class="mt-1" name="title" :value="old('title', $asset->title)" required />
                <x-input-error :messages="$errors->get('title')" class="mt-1" />
            </div>

            <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Uploaded File</span>
                <span class="font-bold text-slate-700 truncate max-w-[250px]">{{ $asset->filename }}</span>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="status" value="Library Status" />
                    <select id="status" name="status" class="auth-input mt-1">
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}" @selected(old('status', $asset->status) === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
                <div>
                    <!-- Place for layout symmetry -->
                </div>
            </div>

            <div>
                <x-input-label for="notes" value="Notes & Description" />
                <textarea id="notes" name="notes" rows="3" class="auth-input mt-1 resize-none h-[80px]" placeholder="Add notes, tags, or upload specifications...">{{ old('notes', $asset->notes) }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-1" />
            </div>

            <div class="flex gap-3 pt-2">
                <x-primary-button>Update</x-primary-button>
                <x-ui.button :href="route('distribution.media.index')" variant="ghost" size="sm">Cancel</x-ui.button>
            </div>
        </form>
    </x-ui.form-card>
</x-ui.page>
