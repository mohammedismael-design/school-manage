<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    protected $model = Module::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'key' => $this->faker->unique()->slug(),
            'icon' => 'AcademicCapIcon',
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement(['core', 'academics', 'finance', 'operations', 'communication']),
            'sort_order' => $this->faker->numberBetween(1, 50),
            'is_core' => false,
            'is_active' => true,
        ];
    }

    public function core(): static
    {
        return $this->state(['is_core' => true, 'category' => 'core']);
    }
}
