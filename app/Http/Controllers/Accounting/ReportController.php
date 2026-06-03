<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();

        $income = Transaction::query()->where('user_id', $userId)->where('type', 'income')->sum('amount');
        $expense = Transaction::query()->where('user_id', $userId)->where('type', 'expense')->sum('amount');

        $byCategory = Transaction::query()
            ->where('user_id', $userId)
            ->select('category', 'type', DB::raw('SUM(amount) as total'))
            ->groupBy('category', 'type')
            ->orderByDesc('total')
            ->get();

        $budgets = Budget::query()->where('user_id', $userId)->latest()->limit(5)->get();

        return view('accounting.reports.index', compact('income', 'expense', 'byCategory', 'budgets'));
    }
}
