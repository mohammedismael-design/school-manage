<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '+254' . $this->faker->numerify('7########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'user_type' => 'staff',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state([
            'user_type' => 'super_admin',
            'tenant_id' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(['user_type' => 'admin']);
    }

    public function teacher(): static
    {
        return $this->state(['user_type' => 'teacher']);
    }

    public function student(): static
    {
        return $this->state(['user_type' => 'student']);
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(['tenant_id' => $tenant->id]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
