<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'password'          => Hash::make('password'),
            'role'              => 'client',
            'phone'             => '06' . $this->faker->numerify('########'),
            'city'              => $this->faker->randomElement(['Casablanca', 'Rabat', 'Marrakech', 'Fès', 'Tanger']),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ];
    }

    public function artisan(): static
    {
        return $this->state(fn(array $attrs) => ['role' => 'artisan']);
    }

    public function client(): static
    {
        return $this->state(fn(array $attrs) => ['role' => 'client']);
    }
}
