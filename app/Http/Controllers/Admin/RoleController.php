<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::query()->orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.create');
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        Role::create(['name' => $request->validated('name')]);

        return redirect()->route('admin.roles.index')->with('status', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $role->update(['name' => $request->validated('name')]);

        return redirect()->route('admin.roles.index')->with('status', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'admin') {
            return redirect()->route('admin.roles.index')->with('status', 'Admin role cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', 'Role deleted successfully.');
    }
}
