<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->word() . ' Budget',
            'category' => fake()->randomElement(['Utilities', 'Marketing', 'Rent Expense', 'Software Subscriptions', 'Travel']),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'period' => fake()->randomElement(['monthly', 'quarterly', 'yearly']),
            'starts_at' => now()->startOfMonth(),
            'ends_at' => now()->endOfMonth(),
        ];
    }
}
