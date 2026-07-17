<x-ui.page title="Upload Media" maxWidth="3xl">
    <x-ui.back-link :href="route('distribution.media.index')" label="Back to Media Library" />
    <x-ui.form-card title="File Details">
        <form method="POST" action="{{ route('distribution.media.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input id="title" class="mt-1" name="title" :value="old('title')" required placeholder="e.g. Podcast Episode #4" />
                <x-input-error :messages="$errors->get('title')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="file" value="Media File" />
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-2xl bg-slate-50/50 hover:bg-slate-50 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-slate-600">
                            <label for="file" class="relative cursor-pointer bg-white rounded-md font-semibold text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                <span class="px-1.5 py-0.5">Upload a file</span>
                                <input id="file" name="file" type="file" required class="sr-only" />
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-slate-400">Audio, Video, or Image up to 10MB</p>
                    </div>
                </div>
                <!-- File name display helper -->
                <p id="file-name-help" class="text-xs text-indigo-600 font-semibold mt-2 hidden"></p>
                <x-input-error :messages="$errors->get('file')" class="mt-1" />
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="status" value="Library Status" />
                    <select id="status" name="status" class="auth-input mt-1">
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}" @selected(old('status', 'ready') === $s)>{{ ucfirst($s) }}</option>
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
                <textarea id="notes" name="notes" rows="3" class="auth-input mt-1 resize-none h-[80px]" placeholder="Add notes, tags, or platform upload specifications...">{{ old('notes') }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-1" />
            </div>

            <div class="flex gap-3 pt-2">
                <x-primary-button>Upload</x-primary-button>
                <x-ui.button :href="route('distribution.media.index')" variant="ghost" size="sm">Cancel</x-ui.button>
            </div>
        </form>
    </x-ui.form-card>

    <script>
        document.getElementById('file').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const helpEl = document.getElementById('file-name-help');
            if (fileName && helpEl) {
                helpEl.innerText = "Selected file: " + fileName;
                helpEl.classList.remove('hidden');
            }
        });
    </script>
</x-ui.page>
