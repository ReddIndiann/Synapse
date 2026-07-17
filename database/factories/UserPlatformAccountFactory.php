<?php

namespace Database\Factories;

use App\Models\DistributionChannel;
use App\Models\User;
use App\Models\UserPlatformAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserPlatformAccountFactory extends Factory
{
    protected $model = UserPlatformAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'distribution_channel_id' => DistributionChannel::factory(),
            'external_account_id' => Str::random(12),
            'account_name' => fake()->company(),
            'account_handle' => fake()->userName(),
            'access_token' => Str::random(40),
            'refresh_token' => Str::random(40),
            'token_expires_at' => now()->addHour(),
            'scopes' => [],
            'metadata' => [],
            'is_active' => true,
            'last_synced_at' => now(),
        ];
    }
}
