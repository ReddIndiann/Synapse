<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_calendar_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('calendar.index'));

        $response->assertOk();
        $response->assertViewHas(['days', 'currentMonthName']);
    }

    public function test_calendar_shows_tasks(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'due_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($this->user)->get(route('calendar.index'));

        $response->assertOk();
    }

    public function test_calendar_shows_transactions(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'occurred_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($this->user)->get(route('calendar.index'));

        $response->assertOk();
    }

    public function test_calendar_supports_month_navigation(): void
    {
        $response = $this->actingAs($this->user)->get(route('calendar.index', ['month' => 1, 'year' => 2026]));

        $response->assertOk();
        $response->assertViewHas('currentMonthName', 'January 2026');
    }

    public function test_calendar_supports_year_boundary_backward(): void
    {
        $response = $this->actingAs($this->user)->get(route('calendar.index', ['month' => 0, 'year' => 2026]));

        $response->assertOk();
        $response->assertViewHas('currentMonthName', 'December 2025');
    }

    public function test_calendar_supports_year_boundary_forward(): void
    {
        $response = $this->actingAs($this->user)->get(route('calendar.index', ['month' => 13, 'year' => 2026]));

        $response->assertOk();
        $response->assertViewHas('currentMonthName', 'January 2027');
    }
}
