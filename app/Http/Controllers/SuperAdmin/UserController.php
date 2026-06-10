<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\PublishJob;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->with('roles');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount(['tasks', 'transactions'])->latest()->paginate(20);

        return view('superadmin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load('roles');
        $stats = [
            'tasks' => Task::where('user_id', $user->id)->count(),
            'pending_tasks' => Task::where('user_id', $user->id)->whereIn('status', ['pending', 'in_progress'])->count(),
            'transactions' => Transaction::where('user_id', $user->id)->count(),
            'income' => Transaction::where('user_id', $user->id)->where('type', 'income')->sum('amount'),
            'expense' => Transaction::where('user_id', $user->id)->where('type', 'expense')->sum('amount'),
            'budgets' => Budget::where('user_id', $user->id)->count(),
            'publish_jobs' => PublishJob::where('user_id', $user->id)->count(),
        ];

        $recentTasks = Task::where('user_id', $user->id)->latest()->limit(5)->get();
        $recentTransactions = Transaction::where('user_id', $user->id)->latest()->limit(5)->get();

        return view('superadmin.users.show', compact('user', 'stats', 'recentTasks', 'recentTransactions'));
    }

    public function create(): View
    {
        $roles = Role::all();

        return view('superadmin.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (!empty($validated['roles'])) {
            $user->assignRole($validated['roles']);
        }

        return redirect()->route('superadmin.users.show', $user)->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $roles = Role::all();

        return view('superadmin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('superadmin.users.show', $user)->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('status', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('superadmin.users.index')->with('status', 'User deleted.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->back()->with('status', 'Password reset successfully.');
    }

    public function impersonate(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('status', 'You cannot impersonate yourself.');
        }

        session()->put('superadmin_original', auth()->id());
        auth()->loginUsingId($user->id);

        return redirect()->route('dashboard')->with('status', "Impersonating {$user->name}. Click user menu to leave.");
    }

    public function leaveImpersonation(): RedirectResponse
    {
        $originalId = session()->pull('superadmin_original');
        if ($originalId) {
            auth()->loginUsingId($originalId);
        }

        return redirect()->route('superadmin.dashboard')->with('status', 'Returned to superadmin session.');
    }
}
