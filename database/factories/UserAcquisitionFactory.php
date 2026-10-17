<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserAcquisition>
 */
class UserAcquisitionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'utm_source' => 'facebook',
            'utm_medium' => 'paid_social',
            'utm_campaign' => fake()->slug(2),
            'utm_content' => fake()->randomElement(['egypt', 'morocco', 'tunisia']),
            'utm_term' => null,
        ];
    }
}
