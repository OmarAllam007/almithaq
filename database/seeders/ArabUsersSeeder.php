<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ArabUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(2000)->arabCountries()->create();
    }
}
