<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Services\AccountingLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingLedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private AccountingLedgerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->service = $this->app->make(AccountingLedgerService::class);
    }

    public function test_profit_and_loss_report(): void
    {
        $expense = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 200.00,
            'payment_method' => 'Cash',
            'category' => 'Rent Expense',
        ]);
        $this->service->recordTransaction($expense);

        $income = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 1000.00,
            'payment_method' => 'Bank',
            'category' => 'Consulting Revenue',
        ]);
        $this->service->recordTransaction($income);

        $pnl = $this->service->getProfitAndLoss($this->user->id);

        $this->assertArrayHasKey('total_revenue', $pnl);
        $this->assertArrayHasKey('total_expense', $pnl);
        $this->assertArrayHasKey('net_income', $pnl);
        $this->assertEquals(1000.00, $pnl['total_revenue']);
        $this->assertEquals(200.00, $pnl['total_expense']);
        $this->assertEquals(800.00, $pnl['net_income']);
    }

    public function test_balance_sheet_is_balanced(): void
    {
        $expense = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 300.00,
            'payment_method' => 'Bank',
            'category' => 'Rent Expense',
        ]);
        $this->service->recordTransaction($expense);

        $income = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 1500.00,
            'payment_method' => 'Cash',
            'category' => 'Consulting Revenue',
        ]);
        $this->service->recordTransaction($income);

        $bs = $this->service->getBalanceSheet($this->user->id);

        $this->assertTrue($bs['is_balanced']);
        $this->assertEquals($bs['total_assets'], $bs['total_liabilities_and_equity']);
    }
}
