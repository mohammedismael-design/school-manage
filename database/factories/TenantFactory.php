<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = $this->faker->company() . ' School';

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(4),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => '+254' . $this->faker->numerify('7########'),
            'address' => $this->faker->address(),
            'status' => 'active',
            'subscription_status' => 'active',
            'plan_id' => null,
            'max_students' => 200,
            'max_staff' => 20,
            'max_storage_mb' => 2048,
            'billing_cycle' => 'monthly',
        ];
    }

    public function suspended(): static
    {
        return $this->state(['status' => 'suspended']);
    }

    public function trial(): static
    {
        return $this->state(['status' => 'trial', 'subscription_status' => 'trial']);
    }
}
