<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <x-input-label for="first_name" :value="__('First Name')" />
        <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name', $user?->first_name)" required />
        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="last_name" :value="__('Last Name')" />
        <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name', $user?->last_name)" />
        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" :value="__('Phone')" />
        <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $user?->phone)" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user?->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="$user ? __('New Password (optional)') : __('Password')" />
        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
    </div>

    <div>
        <x-input-label :value="__('Roles')" />
        <div class="mt-2 space-y-2">
            @foreach ($roles as $role)
                <label class="inline-flex items-center gap-2 mr-4">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(in_array($role->name, old('roles', $selectedRoles), true))>
                    <span>{{ $role->name }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('roles')" class="mt-2" />
        <x-input-error :messages="$errors->get('roles.*')" class="mt-2" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $user ? __('Update User') : __('Create User') }}</x-primary-button>
        <x-ui.button :href="route('admin.users.index')" variant="ghost" size="sm">Cancel</x-ui.button>
    </div>
</form>
