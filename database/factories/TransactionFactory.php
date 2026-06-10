<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['income', 'expense']),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'currency' => 'GHS',
            'category' => fake()->randomElement(['Utilities', 'Rent Expense', 'Marketing', 'Consulting Revenue', 'Product Sales', 'Software Subscriptions']),
            'description' => fake()->sentence(),
            'occurred_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'reference' => null,
            'payment_method' => fake()->randomElement(['Cash', 'Bank', 'Mobile Money']),
            'exchange_rate' => 1.0,
        ];
    }

    public function expense(): static
    {
        return $this->state(fn(array $attr) => ['type' => 'expense']);
    }

    public function income(): static
    {
        return $this->state(fn(array $attr) => ['type' => 'income']);
    }
}
