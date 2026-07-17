<?php

namespace App\Support;

class AccountingCategories
{
    public const INCOME = [
        'Consulting Revenue',
        'Product Sales',
        'Other Income',
    ];

    public const EXPENSE = [
        'Rent Expense',
        'Utilities',
        'Software Subscriptions',
        'Marketing',
        'Travel',
        'General',
    ];

    public const OTHER_VALUE = '__other__';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return array_values(array_unique(array_merge(self::INCOME, self::EXPENSE)));
    }

    /**
     * @return list<string>
     */
    public static function forBudgets(): array
    {
        return self::EXPENSE;
    }

    /**
     * @return list<string>
     */
    public static function forType(?string $type): array
    {
        return match ($type) {
            'income' => self::INCOME,
            'expense' => self::EXPENSE,
            default => self::all(),
        };
    }

    public static function isPreset(string $category): bool
    {
        return in_array($category, self::all(), true);
    }
}
