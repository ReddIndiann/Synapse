<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\TransactionRequest;
use App\Models\Budget;
use App\Models\Transaction;
use App\Notifications\BudgetBreachedNotification;
use App\Services\AccountingLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TransactionController extends Controller
{
    protected $ledgerService;

    public function __construct(AccountingLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function index(Request $request): View
    {
        $userId = auth()->id();

        $query = Transaction::query()->where('user_id', $userId);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $transactions = $query->latest('occurred_at')->paginate(10);

        $income = Transaction::query()->where('user_id', $userId)->where('type', 'income')->sum('amount');
        $expense = Transaction::query()->where('user_id', $userId)->where('type', 'expense')->sum('amount');

        return view('accounting.transactions.index', compact('transactions', 'income', 'expense'));
    }

    public function create(): View
    {
        return view('accounting.transactions.create', [
            'types' => Transaction::types(),
        ]);
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        $transaction = Transaction::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        $this->ledgerService->recordTransaction($transaction);
        $this->checkBudgetBreached($transaction);

        return redirect()->route('accounting.transactions.index')->with('status', 'Transaction recorded.');
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);

        return view('accounting.transactions.edit', [
            'transaction' => $transaction,
            'types' => Transaction::types(),
        ]);
    }

    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $transaction->update($request->validated());

        $this->ledgerService->recordTransaction($transaction);
        $this->checkBudgetBreached($transaction);

        return redirect()->route('accounting.transactions.index')->with('status', 'Transaction updated.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $this->ledgerService->removeTransaction($transaction);
        $transaction->delete();

        return redirect()->route('accounting.transactions.index')->with('status', 'Transaction deleted.');
    }

    private function checkBudgetBreached(Transaction $transaction): void
    {
        if ($transaction->type !== 'expense') {
            return;
        }

        $budget = Budget::where('user_id', $transaction->user_id)
            ->where('category', $transaction->category)
            ->first();

        if (!$budget || $budget->amount <= 0) {
            return;
        }

        $occurredAt = Carbon::parse($transaction->occurred_at);

        $totalSpent = Transaction::where('user_id', $transaction->user_id)
            ->where('category', $transaction->category)
            ->where('type', 'expense')
            ->whereMonth('occurred_at', $occurredAt->month)
            ->whereYear('occurred_at', $occurredAt->year)
            ->sum('amount');

        $level = match (true) {
            $totalSpent > $budget->amount => 'exceeded',
            $totalSpent >= $budget->amount * 0.9 => 'warning_90',
            $totalSpent >= $budget->amount * 0.8 => 'warning_80',
            default => null,
        };

        if (!$level) {
            return;
        }

        $notified = $transaction->user->notifications()
            ->where('type', BudgetBreachedNotification::class)
            ->where('data->budget_id', $budget->id)
            ->where('data->level', $level)
            ->whereMonth('created_at', $occurredAt->month)
            ->whereYear('created_at', $occurredAt->year)
            ->exists();

        if (!$notified) {
            $transaction->user->notify(new BudgetBreachedNotification($budget, $totalSpent, $level));
        }
    }
}
