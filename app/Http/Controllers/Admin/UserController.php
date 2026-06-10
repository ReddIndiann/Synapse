<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->with('roles')->orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $fullName = trim($request->validated('first_name') . ' ' . ($request->validated('last_name') ?? ''));

        $user = User::create([
            'name' => $fullName ?: $request->validated('first_name'),
            'first_name' => $request->validated('first_name'),
            'last_name' => $request->validated('last_name'),
            'phone' => $request->validated('phone'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        $user->syncRoles($request->validated('roles', []));

        return redirect()->route('admin.users.index')->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $fullName = trim($request->validated('first_name') . ' ' . ($request->validated('last_name') ?? ''));

        $user->fill([
            'name' => $fullName ?: $request->validated('first_name'),
            'first_name' => $request->validated('first_name'),
            'last_name' => $request->validated('last_name'),
            'phone' => $request->validated('phone'),
            'email' => $request->validated('email'),
        ]);

        if ($password = $request->validated('password')) {
            $user->password = Hash::make($password);
        }

        $user->save();
        $user->syncRoles($request->validated('roles', []));

        return redirect()->route('admin.users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')->with('status', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User deleted successfully.');
    }
}
