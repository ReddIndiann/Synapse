<?php

namespace Database\Factories;

use App\Models\DistributionChannel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DistributionChannelFactory extends Factory
{
    protected $model = DistributionChannel::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['YouTube', 'Spotify', 'Audiomack', 'Instagram', 'LinkedIn', 'Facebook', 'Website']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'is_active' => true,
        ];
    }
}
