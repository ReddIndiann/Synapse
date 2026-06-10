<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $adminRole = Role::create(['name' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);
    }

    public function test_admin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk();
    }

    public function test_non_admin_cannot_view_users_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => ['admin'],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'first_name' => 'John',
        ]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $otherUser));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $otherUser->id]);
    }

    public function test_admin_can_view_roles_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.roles.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_role(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
            'name' => 'editor',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseHas('roles', ['name' => 'editor']);
    }

    public function test_admin_cannot_delete_admin_role(): void
    {
        $adminRole = Role::findByName('admin');

        $response = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $adminRole));

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
    }
}
