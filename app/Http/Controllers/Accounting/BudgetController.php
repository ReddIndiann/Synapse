<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(): View
    {
        $budgets = Budget::query()->where('user_id', auth()->id())->latest()->paginate(10);

        return view('accounting.budgets.index', compact('budgets'));
    }

    public function create(): View
    {
        return view('accounting.budgets.create', [
            'periods' => Budget::periods(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'period' => ['required', 'in:'.implode(',', Budget::periods())],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        Budget::create([...$validated, 'user_id' => auth()->id()]);

        return redirect()->route('accounting.budgets.index')->with('status', 'Budget created.');
    }

    public function edit(Budget $budget): View
    {
        $this->authorizeBudget($budget);

        return view('accounting.budgets.edit', [
            'budget' => $budget,
            'periods' => Budget::periods(),
        ]);
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        $this->authorizeBudget($budget);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'period' => ['required', 'in:'.implode(',', Budget::periods())],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $budget->update($validated);

        return redirect()->route('accounting.budgets.index')->with('status', 'Budget updated.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorizeBudget($budget);
        $budget->delete();

        return redirect()->route('accounting.budgets.index')->with('status', 'Budget deleted.');
    }

    private function authorizeBudget(Budget $budget): void
    {
        abort_unless($budget->user_id === auth()->id(), 403);
    }
}
