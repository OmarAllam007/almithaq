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
        $registrationType = rand(1, 2);

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'registration_type' => $registrationType,
            'username' => fake()->unique()->userName(),
            'marriage_type' => $registrationType == 1 ? rand(1, 2) : rand(3, 4),
            'marriage_status' => $registrationType == 1 ? rand(1, 4) : 1,
            'age' => fake()->numberBetween(18, 70),
            'child_count' => fake()->numberBetween(0, 10),
            'residence' => rand(1, 10),
            'nationality' => rand(1, 10),
            'city' => rand(1, 10),
            'religion' => 1, // always muslim
            'ethnicity' => 1,
            'weight' => fake()->randomFloat(2, 50, 150),
            'height' => fake()->randomFloat(2, 150, 200),
            'skin_color' => rand(1, 6),
            'body_shape' => rand(1, 5),
            'devotion' => rand(1, 5),
            'prayer' => rand(1, 6),
            'smoking' => rand(0, 1),
            'beard' => $registrationType == 1 ? rand(0, 1) : 0,
            'education_level' => rand(1, 6),
            'financial_status' => rand(1, 5),
            'field_of_work' => rand(1, 18),
            'job' => fake()->jobTitle(),
            'monthly_income' => rand(1, 8),
            'health_status' => rand(1, 26),
            'about_partner' => fake()->paragraph(3),
            'about_self' => fake()->paragraph(3),
            'full_name' => fake()->name(),
            'country_code' => '+20',
            'phone_number' => fake()->phoneNumber(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function arabCountries(): static
    {
        $arabCountryIds = [4, 14, 39, 48, 53, 80, 86, 93, 97, 100, 112, 120, 132, 142, 152, 161, 166, 171, 179, 185, 194];

        return $this->state(fn (array $attributes) => [
            'nationality' => fake()->randomElement($arabCountryIds),
            'residence' => fake()->randomElement($arabCountryIds),
            'city' => null,
            'profile_completed' => true,
        ]);
    }
}
