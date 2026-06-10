<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\BudgetRequest;
use App\Models\Budget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $query = Budget::query()->where('user_id', auth()->id());

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $budgets = $query->latest()->paginate(10);

        return view('accounting.budgets.index', compact('budgets'));
    }

    public function create(): View
    {
        return view('accounting.budgets.create', [
            'periods' => Budget::periods(),
        ]);
    }

    public function store(BudgetRequest $request): RedirectResponse
    {
        Budget::create([...$request->validated(), 'user_id' => auth()->id()]);

        return redirect()->route('accounting.budgets.index')->with('status', 'Budget created.');
    }

    public function edit(Budget $budget): View
    {
        $this->authorize('view', $budget);

        return view('accounting.budgets.edit', [
            'budget' => $budget,
            'periods' => Budget::periods(),
        ]);
    }

    public function update(BudgetRequest $request, Budget $budget): RedirectResponse
    {
        $this->authorize('update', $budget);

        $budget->update($request->validated());

        return redirect()->route('accounting.budgets.index')->with('status', 'Budget updated.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorize('delete', $budget);
        $budget->delete();

        return redirect()->route('accounting.budgets.index')->with('status', 'Budget deleted.');
    }
}
