<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <x-input-label for="name" :value="__('Role Name')" />
        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $role?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $role ? __('Update Role') : __('Create Role') }}</x-primary-button>
        <x-ui.button :href="route('admin.roles.index')" variant="ghost" size="sm">Cancel</x-ui.button>
    </div>
</form>
