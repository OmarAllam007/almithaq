<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'registration_type' => rand(1, 2),
            'username' => fake()->unique()->userName(),
            'marriage_type' => fake()->randomElement(['first', 'second', 'third', 'fourth']),
            'marriage_status' => fake()->randomElement(['single', 'married', 'divorced', 'widowed']),
            'age' => fake()->numberBetween(18, 65),
            'child_count' => fake()->numberBetween(0, 10),
            'residence' => fake()->city(),
            'nationality' => fake()->country(),
            'city' => fake()->city(),
            'religion' => fake()->randomElement(['muslim', 'christian', 'jewish', 'other']),
            'ethnicity' => fake()->randomElement(['arab', 'asian', 'african', 'european', 'other']),
            'weight' => fake()->randomFloat(2, 50, 150),
            'height' => fake()->randomFloat(2, 150, 200),
            'skin_color' => fake()->randomElement(['fair', 'medium', 'olive', 'brown', 'dark']),
            'body_shape' => fake()->randomElement(['slim', 'athletic', 'average', 'heavyset']),
            'devotion' => fake()->randomElement(['very_religious', 'religious', 'moderate', 'not_religious']),
            'prayer' => fake()->randomElement(['always', 'usually', 'sometimes', 'rarely', 'never']),
            'smoking' => fake()->randomElement(['yes', 'no', 'occasionally']),
            'beard' => fake()->randomElement(['yes', 'no', 'trimmed']),
            'education_level' => fake()->randomElement(['high_school', 'bachelor', 'master', 'phd']),
            'financial_status' => fake()->randomElement(['excellent', 'good', 'average', 'below_average']),
            'field_of_work' => fake()->randomElement(['technology', 'healthcare', 'education', 'business', 'engineering', 'other']),
            'job' => fake()->jobTitle(),
            'monthly_income' => fake()->randomFloat(2, 1000, 50000),
            'health_status' => fake()->randomElement(['excellent', 'good', 'fair', 'poor']),
            'about_partner' => fake()->paragraph(3),
            'about_self' => fake()->paragraph(3),
            'full_name' => fake()->name(),
            'phone_number' => fake()->phoneNumber(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
