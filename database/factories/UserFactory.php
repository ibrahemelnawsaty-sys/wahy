<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\School;
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
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'role'              => UserRole::Student->value,
            'status'            => 'active',
            'remember_token'    => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'role'      => UserRole::SuperAdmin->value,
            'school_id' => null,
        ]);
    }

    public function schoolAdmin(?School $school = null): static
    {
        return $this->state(fn () => [
            'role'      => UserRole::SchoolAdmin->value,
            'school_id' => ($school ?? School::factory()->create())->id,
        ]);
    }

    public function teacher(?School $school = null): static
    {
        return $this->state(fn () => [
            'role'      => UserRole::Teacher->value,
            'school_id' => ($school ?? School::factory()->create())->id,
        ]);
    }

    public function student(?School $school = null): static
    {
        return $this->state(fn () => [
            'role'      => UserRole::Student->value,
            'school_id' => ($school ?? School::factory()->create())->id,
        ]);
    }

    public function parent(?School $school = null): static
    {
        return $this->state(fn () => [
            'role'      => UserRole::Parent->value,
            'school_id' => ($school ?? School::factory()->create())->id,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
