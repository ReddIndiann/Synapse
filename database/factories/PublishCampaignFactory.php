<?php

namespace Database\Factories;

use App\Models\MediaAsset;
use App\Models\PublishCampaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublishCampaignFactory extends Factory
{
    protected $model = PublishCampaign::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'media_asset_id' => MediaAsset::factory(),
            'caption' => fake()->sentence(),
            'scheduled_at' => null,
            'status' => 'queued',
            'record_cost' => false,
            'estimated_cost_per_channel' => null,
            'currency' => 'GHS',
        ];
    }
}
