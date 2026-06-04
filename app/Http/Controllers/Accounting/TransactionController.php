<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\AccountingLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    protected $ledgerService;

    public function __construct(AccountingLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function index(): View
    {
        $transactions = Transaction::query()
            ->where('user_id', auth()->id())
            ->latest('occurred_at')
            ->paginate(10);

        $income = Transaction::query()->where('user_id', auth()->id())->where('type', 'income')->sum('amount');
        $expense = Transaction::query()->where('user_id', auth()->id())->where('type', 'expense')->sum('amount');

        return view('accounting.transactions.index', compact('transactions', 'income', 'expense'));
    }

    public function create(): View
    {
        return view('accounting.transactions.create', [
            'types' => Transaction::types(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:'.implode(',', Transaction::types())],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'occurred_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'in:Cash,Bank,Mobile Money'],
            'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
        ]);

        $transaction = Transaction::create([
            ...$validated,
            'user_id' => auth()->id()
        ]);

        // Sync with double entry ledger
        $this->ledgerService->recordTransaction($transaction);

        // Check for budget breaches
        $this->checkBudgetBreached($transaction);

        return redirect()->route('accounting.transactions.index')->with('status', 'Transaction recorded.');
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorizeTransaction($transaction);

        return view('accounting.transactions.edit', [
            'transaction' => $transaction,
            'types' => Transaction::types(),
        ]);
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $validated = $request->validate([
            'type' => ['required', 'in:'.implode(',', Transaction::types())],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'occurred_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'in:Cash,Bank,Mobile Money'],
            'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
        ]);

        $transaction->update($validated);

        // Sync with double entry ledger
        $this->ledgerService->recordTransaction($transaction);

        // Check for budget breaches
        $this->checkBudgetBreached($transaction);

        return redirect()->route('accounting.transactions.index')->with('status', 'Transaction updated.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        // Delete ledger records first
        $this->ledgerService->removeTransaction($transaction);
        
        $transaction->delete();

        return redirect()->route('accounting.transactions.index')->with('status', 'Transaction deleted.');
    }

    private function authorizeTransaction(Transaction $transaction): void
    {
        abort_unless($transaction->user_id === auth()->id(), 403);
    }

    private function checkBudgetBreached(Transaction $transaction): void
    {
        if ($transaction->type !== 'expense') {
            return;
        }

        $budget = \App\Models\Budget::where('user_id', $transaction->user_id)
            ->where('category', $transaction->category)
            ->first();

        if ($budget) {
            $occurredAt = \Illuminate\Support\Carbon::parse($transaction->occurred_at);
            
            $totalSpent = Transaction::where('user_id', $transaction->user_id)
                ->where('category', $transaction->category)
                ->where('type', 'expense')
                ->whereMonth('occurred_at', $occurredAt->month)
                ->whereYear('occurred_at', $occurredAt->year)
                ->sum('amount');

            if ($totalSpent > $budget->amount) {
                $transaction->user->notify(new \App\Notifications\BudgetBreachedNotification($budget, $totalSpent));
            }
        }
    }
}
