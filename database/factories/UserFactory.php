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
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Cria um user com is_assinante=true e atribui a role `assinante`.
     */
    public function assinante(): static
    {
        return $this->state(fn () => [
            'is_assinante'    => true,
            'numero_portaria' => 'PORT-' . fake()->numberBetween(100, 999) . '/2026',
            'data_portaria'   => fake()->dateTimeBetween('-2 years')->format('Y-m-d'),
        ])->afterCreating(function ($user) {
            $role = \Spatie\Permission\Models\Role::firstOrCreate([
                'name'       => 'assinante',
                'guard_name' => 'web',
            ]);
            if (!$user->hasRole($role)) {
                $user->assignRole($role);
            }
        });
    }
}
