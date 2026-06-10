<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TaskUpcomingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_notifications_index_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('notifications.index'));

        $response->assertOk();
    }

    public function test_notification_can_be_marked_as_read(): void
    {
        $notification = $this->user->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => TaskUpcomingNotification::class,
            'data' => ['task_id' => 1],
        ]);

        $response = $this->actingAs($this->user)->post(route('notifications.read', $notification->id));

        $response->assertSessionHasNoErrors();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_all_notifications_can_be_marked_as_read(): void
    {
        $this->user->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => TaskUpcomingNotification::class,
            'data' => ['task_id' => 1],
        ]);

        $this->user->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => TaskUpcomingNotification::class,
            'data' => ['task_id' => 2],
        ]);

        $response = $this->actingAs($this->user)->post(route('notifications.read-all'));

        $response->assertSessionHasNoErrors();
        $this->assertEquals(0, $this->user->fresh()->unreadNotifications->count());
    }

    public function test_notification_can_be_deleted(): void
    {
        $notification = $this->user->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => TaskUpcomingNotification::class,
            'data' => ['task_id' => 1],
        ]);

        $response = $this->actingAs($this->user)->delete(route('notifications.destroy', $notification->id));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_all_notifications_can_be_cleared(): void
    {
        $this->user->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => TaskUpcomingNotification::class,
            'data' => ['task_id' => 1],
        ]);

        $response = $this->actingAs($this->user)->delete(route('notifications.clear-all'));

        $response->assertSessionHasNoErrors();
        $this->assertEquals(0, $this->user->notifications()->count());
    }
}
