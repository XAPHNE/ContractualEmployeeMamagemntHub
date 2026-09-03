<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeContribution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeContribution>
 */
class EmployeeContributionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'month' => $this->faker->numberBetween(1, 12),
            'fin_year' => '2024-25',
            'contribution_amount' => 225.00,
            'contribution_date' => $this->faker->date('Y-m-d'),
        ];
    }
}
