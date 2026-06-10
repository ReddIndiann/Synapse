<?php

namespace Tests\Feature;

use App\Models\AssistantMessage;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistantTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_chat_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('assistant.chat'));

        $response->assertOk();
        $response->assertViewHas('messages');
    }

    public function test_chat_creates_welcome_message_on_first_visit(): void
    {
        $this->actingAs($this->user)->get(route('assistant.chat'));

        $this->assertDatabaseHas('assistant_messages', [
            'user_id' => $this->user->id,
            'role' => 'assistant',
        ]);
    }

    public function test_chat_requires_prompt(): void
    {
        $response = $this->actingAs($this->user)->post(route('assistant.chat.store'), []);

        $response->assertSessionHasErrors('prompt');
    }

    public function test_chat_stores_user_message(): void
    {
        $this->actingAs($this->user)->post(route('assistant.chat.store'), [
            'prompt' => 'Schedule a meeting tomorrow at 10 AM',
        ]);

        $this->assertDatabaseHas('assistant_messages', [
            'user_id' => $this->user->id,
            'role' => 'user',
            'content' => 'Schedule a meeting tomorrow at 10 AM',
        ]);
    }

    public function test_chat_can_be_cleared(): void
    {
        AssistantMessage::create([
            'user_id' => $this->user->id,
            'role' => 'user',
            'content' => 'Test message',
        ]);

        $this->actingAs($this->user)->post(route('assistant.chat.clear'));

        $this->assertDatabaseMissing('assistant_messages', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_task_index_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('assistant.tasks.index'));

        $response->assertOk();
    }

    public function test_task_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('assistant.tasks.store'), [
            'title' => 'Test Task',
            'description' => 'Task description',
            'priority' => 'high',
            'status' => 'pending',
            'due_at' => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('assistant.tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'user_id' => $this->user->id,
            'title' => 'Test Task',
            'priority' => 'high',
        ]);
    }

    public function test_task_requires_valid_priority(): void
    {
        $response = $this->actingAs($this->user)->post(route('assistant.tasks.store'), [
            'title' => 'Test',
            'priority' => 'urgent',
            'status' => 'pending',
        ]);

        $response->assertSessionHasErrors('priority');
    }

    public function test_task_can_be_updated(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->put(route('assistant.tasks.update', $task), [
            'title' => 'Updated Task',
            'description' => 'Updated',
            'priority' => 'low',
            'status' => 'in_progress',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task',
            'status' => 'in_progress',
        ]);
    }

    public function test_task_can_be_deleted(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('assistant.tasks.destroy', $task));

        $response->assertRedirect(route('assistant.tasks.index'));
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_access_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->get(route('assistant.tasks.edit', $task));

        $response->assertForbidden();
    }

    public function test_upcoming_alerts_returns_json(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'due_at' => now()->addMinutes(3),
        ]);

        $response = $this->actingAs($this->user)->get(route('assistant.tasks.upcoming-alerts'));

        $response->assertOk();
        $response->assertJsonStructure([['id', 'title', 'due_at', 'threshold', 'minutes_remaining']]);
    }

    public function test_task_status_update_via_json(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->put(
            route('assistant.tasks.update', $task),
            ['status' => 'completed'],
            ['Accept' => 'application/json']
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'completed']);
    }
}
