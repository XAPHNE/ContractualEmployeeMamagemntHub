<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'dept_id' => (string) $this->faker->unique()->numberBetween(100, 99999),
            'name' => $this->faker->unique()->company().' Department',
            'code' => strtoupper($this->faker->lexify('???')),
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
        ];
    }
}
