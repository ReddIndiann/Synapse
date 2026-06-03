<?php

namespace App\Services;

use App\Models\LedgerAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class AccountingLedgerService
{
    /**
     * Record a transaction in the double-entry general ledger.
     */
    public function recordTransaction(Transaction $transaction): JournalEntry
    {
        return DB::transaction(function () use ($transaction) {
            $baseAmount = $transaction->amount * $transaction->exchange_rate;

            // 1. Create or Update Journal Entry
            $journalEntry = JournalEntry::updateOrCreate(
                [
                    'user_id' => $transaction->user_id,
                    'reference' => "transaction_{$transaction->id}",
                ],
                [
                    'description' => $transaction->description ?: "Transaction for {$transaction->category}",
                    'occurred_at' => $transaction->occurred_at,
                    'currency' => $transaction->currency,
                    'amount' => $transaction->amount,
                    'exchange_rate' => $transaction->exchange_rate,
                    'base_currency' => 'GHS',
                    'base_amount' => $baseAmount,
                ]
            );

            // Clear old lines if modifying
            $journalEntry->lines()->delete();

            // 2. Resolve or Create Ledger Accounts
            $paymentMethod = $transaction->payment_method ?: 'Cash';
            $assetAccount = $this->resolveAccount($transaction->user_id, $paymentMethod, 'asset');
            
            $category = $transaction->category ?: 'General';
            $categoryType = $transaction->type === 'income' ? 'revenue' : 'expense';
            $nominalAccount = $this->resolveAccount($transaction->user_id, $category, $categoryType);

            // 3. Post Double-Entry Journal Lines
            if ($transaction->type === 'income') {
                // Income: Debit Asset (increases), Credit Revenue (increases)
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'ledger_account_id' => $assetAccount->id,
                    'entry_type' => 'debit',
                    'amount' => $transaction->amount,
                    'base_amount' => $baseAmount,
                ]);

                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'ledger_account_id' => $nominalAccount->id,
                    'entry_type' => 'credit',
                    'amount' => $transaction->amount,
                    'base_amount' => $baseAmount,
                ]);
            } else {
                // Expense: Debit Expense (increases), Credit Asset (decreases)
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'ledger_account_id' => $nominalAccount->id,
                    'entry_type' => 'debit',
                    'amount' => $transaction->amount,
                    'base_amount' => $baseAmount,
                ]);

                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'ledger_account_id' => $assetAccount->id,
                    'entry_type' => 'credit',
                    'amount' => $transaction->amount,
                    'base_amount' => $baseAmount,
                ]);
            }

            return $journalEntry;
        });
    }

    /**
     * Remove transaction journal entries.
     */
    public function removeTransaction(Transaction $transaction): void
    {
        JournalEntry::query()
            ->where('user_id', $transaction->user_id)
            ->where('reference', "transaction_{$transaction->id}")
            ->delete(); // Cascades to lines
    }

    /**
     * Resolve account by name or create default.
     */
    private function resolveAccount(int $userId, string $name, string $type): LedgerAccount
    {
        $codeMap = [
            'Cash' => '1000',
            'Bank' => '1010',
            'Bank Account' => '1010',
            'Mobile Money' => '1020',
            'Consulting Revenue' => '4000',
            'Product Sales' => '4100',
            'Other Income' => '4200',
            'Rent Expense' => '5000',
            'Utilities' => '5010',
            'Software Subscriptions' => '5020',
            'Marketing' => '5030',
            'Travel' => '5040',
        ];

        // Normalise name matches
        $matchedCode = null;
        foreach ($codeMap as $key => $code) {
            if (strtolower($key) === strtolower($name) || strtolower($key) . ' expense' === strtolower($name) || strtolower($key) . ' revenue' === strtolower($name)) {
                $matchedCode = $code;
                $name = $key;
                break;
            }
        }

        if (!$matchedCode) {
            // Generate a random code in type range
            $prefix = $type === 'asset' ? '1' : ($type === 'liability' ? '2' : ($type === 'equity' ? '3' : ($type === 'revenue' ? '4' : '5')));
            $matchedCode = $prefix . str_pad(rand(50, 999), 3, '0', STR_PAD_LEFT);
        }

        return LedgerAccount::firstOrCreate(
            ['user_id' => $userId, 'name' => $name],
            [
                'user_id' => $userId,
                'code' => $matchedCode,
                'name' => $name,
                'type' => $type,
                'currency' => 'GHS',
            ]
        );
    }

    /**
     * Get Trial Balance (Verification Report).
     */
    public function getTrialBalance(int $userId): array
    {
        $accounts = LedgerAccount::where('user_id', $userId)->get();
        $rows = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($accounts as $account) {
            $debitSum = JournalLine::where('ledger_account_id', $account->id)
                ->where('entry_type', 'debit')
                ->sum('base_amount');

            $creditSum = JournalLine::where('ledger_account_id', $account->id)
                ->where('entry_type', 'credit')
                ->sum('base_amount');

            if ($debitSum > 0 || $creditSum > 0) {
                $rows[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'debit' => $debitSum,
                    'credit' => $creditSum,
                ];
                $totalDebits += $debitSum;
                $totalCredits += $creditSum;
            }
        }

        return [
            'rows' => $rows,
            'total_debit' => $totalDebits,
            'total_credit' => $totalCredits,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
        ];
    }

    /**
     * Get Profit & Loss Report (Income Statement).
     */
    public function getProfitAndLoss(int $userId): array
    {
        $accounts = LedgerAccount::where('user_id', $userId)->get();
        $revenues = [];
        $expenses = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($accounts as $account) {
            if ($account->type === 'revenue') {
                // Revenue: Credit balance (credit - debit)
                $credits = JournalLine::where('ledger_account_id', $account->id)->where('entry_type', 'credit')->sum('base_amount');
                $debits = JournalLine::where('ledger_account_id', $account->id)->where('entry_type', 'debit')->sum('base_amount');
                $balance = $credits - $debits;
                if ($balance > 0) {
                    $revenues[] = ['name' => $account->name, 'amount' => $balance];
                    $totalRevenue += $balance;
                }
            } elseif ($account->type === 'expense') {
                // Expense: Debit balance (debit - credit)
                $debits = JournalLine::where('ledger_account_id', $account->id)->where('entry_type', 'debit')->sum('base_amount');
                $credits = JournalLine::where('ledger_account_id', $account->id)->where('entry_type', 'credit')->sum('base_amount');
                $balance = $debits - $credits;
                if ($balance > 0) {
                    $expenses[] = ['name' => $account->name, 'amount' => $balance];
                    $totalExpense += $balance;
                }
            }
        }

        return [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net_income' => $totalRevenue - $totalExpense,
        ];
    }

    /**
     * Get Balance Sheet Report.
     */
    public function getBalanceSheet(int $userId): array
    {
        $accounts = LedgerAccount::where('user_id', $userId)->get();
        $assets = [];
        $liabilities = [];
        $equities = [];
        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquity = 0;

        // Fetch P&L net income as Retained Earnings
        $pnl = $this->getProfitAndLoss($userId);
        $retainedEarnings = $pnl['net_income'];

        foreach ($accounts as $account) {
            $debits = JournalLine::where('ledger_account_id', $account->id)->where('entry_type', 'debit')->sum('base_amount');
            $credits = JournalLine::where('ledger_account_id', $account->id)->where('entry_type', 'credit')->sum('base_amount');

            if ($account->type === 'asset') {
                // Asset: Debit balance (debit - credit)
                $balance = $debits - $credits;
                if ($balance != 0) {
                    $assets[] = ['name' => $account->name, 'amount' => $balance];
                    $totalAssets += $balance;
                }
            } elseif ($account->type === 'liability') {
                // Liability: Credit balance (credit - debit)
                $balance = $credits - $debits;
                if ($balance != 0) {
                    $liabilities[] = ['name' => $account->name, 'amount' => $balance];
                    $totalLiabilities += $balance;
                }
            } elseif ($account->type === 'equity') {
                // Equity: Credit balance (credit - debit)
                $balance = $credits - $debits;
                if ($balance != 0) {
                    $equities[] = ['name' => $account->name, 'amount' => $balance];
                    $totalEquity += $balance;
                }
            }
        }

        // Add Retained Earnings (Net Income) to Equity
        if ($retainedEarnings != 0) {
            $equities[] = ['name' => 'Retained Earnings (Net Income)', 'amount' => $retainedEarnings];
            $totalEquity += $retainedEarnings;
        }

        return [
            'assets' => $assets,
            'total_assets' => $totalAssets,
            'liabilities' => $liabilities,
            'total_liabilities' => $totalLiabilities,
            'equities' => $equities,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }
}
