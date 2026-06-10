<?php

namespace Database\Factories;

use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaAssetFactory extends Factory
{
    protected $model = MediaAsset::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'filename' => fake()->word() . '.' . fake()->fileExtension(),
            'path' => 'media/' . fake()->uuid() . '/' . fake()->word() . '.' . fake()->fileExtension(),
            'mime_type' => fake()->randomElement(['image/jpeg', 'image/png', 'video/mp4', 'audio/mp3', 'application/pdf']),
            'size' => fake()->numberBetween(1024, 10485760),
            'status' => fake()->randomElement(['draft', 'processing', 'ready']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
