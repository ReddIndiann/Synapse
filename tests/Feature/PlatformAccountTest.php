<?php

namespace Tests\Feature;

use App\Models\DistributionChannel;
use App\Models\User;
use App\Models\UserPlatformAccount;
use App\Services\Distribution\PlatformOAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class PlatformAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_connected_accounts_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('distribution.accounts.index'));

        $response->assertOk();
    }

    public function test_oauth_callback_stores_platform_account(): void
    {
        config([
            'distribution.oauth.youtube.client_id' => 'test-client',
            'distribution.oauth.youtube.client_secret' => 'test-secret',
        ]);

        $user = User::factory()->create();
        $channel = DistributionChannel::factory()->create(['slug' => 'youtube', 'is_active' => true]);

        Session::put('oauth_state.youtube', 'valid-state');
        Session::put('oauth_channel.youtube', $channel->id);

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'access-123',
                'refresh_token' => 'refresh-123',
                'expires_in' => 3600,
            ]),
            'www.googleapis.com/youtube/v3/channels*' => Http::response([
                'items' => [[
                    'id' => 'channel-abc',
                    'snippet' => ['title' => 'My Channel', 'customUrl' => '@mychannel'],
                ]],
            ]),
        ]);

        $response = $this->actingAs($user)->get(route('distribution.accounts.callback', [
            'platform' => 'youtube',
            'code' => 'auth-code',
            'state' => 'valid-state',
        ]));

        $response->assertRedirect(route('distribution.accounts.index'));
        $this->assertDatabaseHas('user_platform_accounts', [
            'user_id' => $user->id,
            'distribution_channel_id' => $channel->id,
            'external_account_id' => 'channel-abc',
            'account_name' => 'My Channel',
        ]);
    }

    public function test_user_can_disconnect_platform_account(): void
    {
        $user = User::factory()->create();
        $account = UserPlatformAccount::factory()->create(['user_id' => $user->id, 'is_active' => true]);

        $response = $this->actingAs($user)->delete(route('distribution.accounts.disconnect', $account));

        $response->assertRedirect();
        $account->refresh();
        $this->assertFalse($account->is_active);
    }

    public function test_platform_card_action_connects_when_oauth_configured(): void
    {
        config([
            'distribution.oauth.youtube.client_id' => 'test-client',
            'distribution.oauth.youtube.client_secret' => 'test-secret',
        ]);

        $channel = DistributionChannel::factory()->create(['slug' => 'youtube', 'is_active' => true]);
        $action = app(PlatformOAuthService::class)->cardAction($channel, null);

        $this->assertSame('connect', $action['type']);
        $this->assertStringContainsString('accounts/connect', $action['url']);
    }

    public function test_platform_card_action_external_when_oauth_not_configured(): void
    {
        $channel = DistributionChannel::factory()->create(['slug' => 'facebook', 'is_active' => true]);
        $action = app(PlatformOAuthService::class)->cardAction($channel, null);

        $this->assertSame('external', $action['type']);
        $this->assertTrue($action['external']);
        $this->assertStringStartsWith('https://', $action['url']);
    }

    public function test_connected_account_details_are_displayed_on_cards(): void
    {
        $user = User::factory()->create();
        $channel = DistributionChannel::factory()->create(['name' => 'YouTube', 'slug' => 'youtube', 'is_active' => true]);
        UserPlatformAccount::factory()->create([
            'user_id' => $user->id,
            'distribution_channel_id' => $channel->id,
            'account_name' => 'My YouTube Channel',
            'account_handle' => '@mychannel',
            'external_account_id' => 'UC123456',
            'is_active' => true,
            'last_synced_at' => now(),
            'token_expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($user)->get(route('distribution.accounts.index'));

        $response->assertOk();
        $response->assertSee('My YouTube Channel');
        $response->assertSee('@mychannel');
        $response->assertSee('UC123456');
        $response->assertSee('Account details');
        $response->assertSee('Disconnect');
        $response->assertSee('View profile');
    }
}
