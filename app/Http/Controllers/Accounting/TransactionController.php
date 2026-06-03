<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
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
        ]);

        Transaction::create([...$validated, 'user_id' => auth()->id()]);

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
        ]);

        $transaction->update($validated);

        return redirect()->route('accounting.transactions.index')->with('status', 'Transaction updated.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);
        $transaction->delete();

        return redirect()->route('accounting.transactions.index')->with('status', 'Transaction deleted.');
    }

    private function authorizeTransaction(Transaction $transaction): void
    {
        abort_unless($transaction->user_id === auth()->id(), 403);
    }
}
