<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

class BudgetService
{
    /**
     * Calculate total expense spend for a budget within its active period.
     */
    public function spentForBudget(Budget $budget, ?Carbon $asOf = null): float
    {
        $asOf = $asOf ?? Carbon::now();
        [$start, $end] = $this->periodBounds($budget, $asOf);

        return (float) Transaction::query()
            ->where('user_id', $budget->user_id)
            ->where('category', $budget->category)
            ->where('type', 'expense')
            ->whereBetween('occurred_at', [$start, $end])
            ->sum('amount');
    }

    public function remainingForBudget(Budget $budget, ?Carbon $asOf = null): float
    {
        return max(0, (float) $budget->amount - $this->spentForBudget($budget, $asOf));
    }

    public function utilizationPercent(Budget $budget, ?Carbon $asOf = null): float
    {
        if ((float) $budget->amount <= 0) {
            return 0;
        }

        return min(100, ($this->spentForBudget($budget, $asOf) / (float) $budget->amount) * 100);
    }

    public function breachLevel(Budget $budget, float $totalSpent): ?string
    {
        if ($budget->amount <= 0) {
            return null;
        }

        return match (true) {
            $totalSpent > $budget->amount => 'exceeded',
            $totalSpent >= $budget->amount * 0.9 => 'warning_90',
            $totalSpent >= $budget->amount * 0.8 => 'warning_80',
            default => null,
        };
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function periodBounds(Budget $budget, Carbon $asOf): array
    {
        if ($budget->starts_at && $budget->ends_at) {
            return [
                Carbon::parse($budget->starts_at)->startOfDay(),
                Carbon::parse($budget->ends_at)->endOfDay(),
            ];
        }

        return match ($budget->period) {
            'quarterly' => [$asOf->copy()->firstOfQuarter()->startOfDay(), $asOf->copy()->lastOfQuarter()->endOfDay()],
            'yearly' => [$asOf->copy()->startOfYear(), $asOf->copy()->endOfYear()],
            default => [$asOf->copy()->startOfMonth(), $asOf->copy()->endOfMonth()],
        };
    }

    public function marketingBudgetSummary(int $userId): ?array
    {
        $budget = Budget::query()
            ->where('user_id', $userId)
            ->where('category', 'Marketing')
            ->first();

        if (!$budget) {
            return null;
        }

        $spent = $this->spentForBudget($budget);
        $remaining = $this->remainingForBudget($budget);

        return [
            'budget' => $budget,
            'spent' => $spent,
            'remaining' => $remaining,
            'limit' => (float) $budget->amount,
            'utilization' => $this->utilizationPercent($budget),
        ];
    }
}
