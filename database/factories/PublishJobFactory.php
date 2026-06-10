<?php

namespace Database\Factories;

use App\Models\DistributionChannel;
use App\Models\MediaAsset;
use App\Models\PublishJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublishJobFactory extends Factory
{
    protected $model = PublishJob::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'media_asset_id' => MediaAsset::factory(),
            'distribution_channel_id' => DistributionChannel::factory(),
            'status' => fake()->randomElement(['pending', 'scheduled', 'published', 'failed']),
            'caption' => fake()->sentence(),
            'scheduled_at' => null,
            'published_at' => null,
            'published_url' => null,
            'logs' => [],
        ];
    }
}
