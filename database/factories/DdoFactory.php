<?php

namespace Database\Factories;

use App\Models\Ddo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ddo>
 */
class DdoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ddoId' => $this->faker->unique()->numerify('########'),
            'ddoName' => $this->faker->name,
            'pan' => $this->faker->regexify('[A-Z]{5}[0-9]{4}[A-Z]{1}'),
            'departmentName' => $this->faker->randomElement([
                'Generation',
                'PP&I',
                'H&C',
                'F&A',
                'HR',
                'Procurement',
                'TRC',
                'IT Cell',
                'Legal',
                'NTPS',
                'LTPS',
                'KLHEP',
                'LKHEP'
            ]),
            'directorate' => $this->faker->optional()->bs,
            'postName' => $this->faker->jobTitle,
            'officeName' => $this->faker->company,
            'officeAddress' => $this->faker->address,
            'mobileNumber' => $this->faker->numerify('##########'),
            'treasuryName' => $this->faker->optional()->company,
            'treasuryCode' => $this->faker->optional()->bothify('T-####'),
            'email' => $this->faker->unique()->safeEmail,
            'districtName' => $this->faker->city,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'deleted_by' => null,
        ];
    }
}
