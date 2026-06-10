<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_root_redirects_unauthenticated_users(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_root_redirects_authenticated_users_to_dashboard(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('dashboard'));
    }
}
