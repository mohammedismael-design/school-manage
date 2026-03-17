<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'code' => $this->faker->unique()->slug(),
            'description' => $this->faker->sentence(),
            'price_monthly' => $this->faker->numberBetween(1000, 50000),
            'price_yearly' => $this->faker->numberBetween(10000, 500000),
            'student_limit' => $this->faker->numberBetween(50, 1000),
            'staff_limit' => $this->faker->numberBetween(5, 100),
            'storage_limit_mb' => $this->faker->numberBetween(1024, 51200),
            'features' => [],
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function enterprise(): static
    {
        return $this->state([
            'student_limit' => 0,
            'staff_limit' => 0,
            'storage_limit_mb' => 0,
        ]);
    }
}
