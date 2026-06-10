<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AccountingLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_transaction_index_page_is_displayed(): void
    {
        Transaction::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('accounting.transactions.index'));

        $response->assertOk();
        $response->assertViewHas('transactions');
    }

    public function test_transaction_can_be_created_with_ledger_sync(): void
    {
        $response = $this->actingAs($this->user)->post(route('accounting.transactions.store'), [
            'type' => 'expense',
            'amount' => 150.00,
            'currency' => 'GHS',
            'category' => 'Utilities',
            'description' => 'Internet bill',
            'occurred_at' => '2026-06-01',
            'payment_method' => 'Mobile Money',
            'exchange_rate' => 1.0,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('accounting.transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 150.00,
            'category' => 'Utilities',
        ]);

        $transaction = Transaction::where('user_id', $this->user->id)->first();
        $this->assertDatabaseHas('journal_entries', [
            'user_id' => $this->user->id,
            'reference' => "transaction_{$transaction->id}",
        ]);
    }

    public function test_transaction_requires_valid_type(): void
    {
        $response = $this->actingAs($this->user)->post(route('accounting.transactions.store'), [
            'type' => 'invalid_type',
            'amount' => 100,
            'currency' => 'GHS',
            'category' => 'Test',
            'occurred_at' => '2026-06-01',
            'payment_method' => 'Cash',
            'exchange_rate' => 1.0,
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_transaction_requires_amount_minimum(): void
    {
        $response = $this->actingAs($this->user)->post(route('accounting.transactions.store'), [
            'type' => 'expense',
            'amount' => 0,
            'currency' => 'GHS',
            'category' => 'Test',
            'occurred_at' => '2026-06-01',
            'payment_method' => 'Cash',
            'exchange_rate' => 1.0,
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_transaction_can_be_updated(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100.00,
        ]);

        $response = $this->actingAs($this->user)->put(route('accounting.transactions.update', $transaction), [
            'type' => 'expense',
            'amount' => 200.00,
            'currency' => 'GHS',
            'category' => 'Utilities',
            'description' => 'Updated bill',
            'occurred_at' => '2026-06-01',
            'payment_method' => 'Cash',
            'exchange_rate' => 1.0,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('accounting.transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 200.00,
        ]);
    }

    public function test_transaction_can_be_deleted(): void
    {
        $transaction = Transaction::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('accounting.transactions.destroy', $transaction));

        $response->assertRedirect(route('accounting.transactions.index'));
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_user_cannot_access_other_users_transaction(): void
    {
        $otherUser = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->get(route('accounting.transactions.edit', $transaction));

        $response->assertForbidden();
    }

    public function test_budget_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('accounting.budgets.store'), [
            'name' => 'Monthly Internet',
            'category' => 'Utilities',
            'amount' => 500.00,
            'period' => 'monthly',
            'starts_at' => '2026-06-01',
            'ends_at' => '2026-06-30',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('accounting.budgets.index'));

        $this->assertDatabaseHas('budgets', [
            'user_id' => $this->user->id,
            'category' => 'Utilities',
            'amount' => 500.00,
        ]);
    }

    public function test_budget_requires_valid_period(): void
    {
        $response = $this->actingAs($this->user)->post(route('accounting.budgets.store'), [
            'name' => 'Test',
            'category' => 'Test',
            'amount' => 100,
            'period' => 'invalid',
        ]);

        $response->assertSessionHasErrors('period');
    }

    public function test_budget_can_be_deleted(): void
    {
        $budget = Budget::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('accounting.budgets.destroy', $budget));

        $response->assertRedirect(route('accounting.budgets.index'));
        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }

    public function test_reports_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('accounting.reports.index'));

        $response->assertOk();
    }

    public function test_budget_breach_notification_is_sent(): void
    {
        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'Utilities',
            'amount' => 100.00,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'Utilities',
            'type' => 'expense',
            'amount' => 90.00,
            'occurred_at' => now(),
        ]);

        $this->actingAs($this->user)->post(route('accounting.transactions.store'), [
            'type' => 'expense',
            'amount' => 20.00,
            'currency' => 'GHS',
            'category' => 'Utilities',
            'occurred_at' => now()->toDateString(),
            'payment_method' => 'Cash',
            'exchange_rate' => 1.0,
        ]);

        $this->assertDatabaseHas('notifications', [
            'type' => 'App\Notifications\BudgetBreachedNotification',
        ]);
    }
}
