<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        $fullName = "{$firstName} {$lastName}";

        return [
            'emp_id' => $this->faker->unique()->numerify('EMP######'),
            'full_Name' => $fullName,
            'first_Name' => $firstName,
            'middle_Name' => '',
            'last_Name' => $lastName,
            'type' => $this->faker->randomElement(['Contractual', 'Fixed Pay', 'Consolidated']),
            'mobile' => $this->faker->numerify('9#########'),
            'employee_code' => $this->faker->unique()->bothify('CODE-#####'),
            'pan' => $this->faker->regexify('[A-Z]{5}[0-9]{4}[A-Z]{1}'),
            'gender' => $this->faker->randomElement(['Male', 'Female', 'Other']),
            'dob' => $this->faker->date('Y-m-d', '-20 years'),
            'designation' => $this->faker->jobTitle,
            'grade' => $this->faker->randomElement(['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4']),
            'pay_band' => 'PB-1',
            'grade_pay' => '4400',
            'date_of_joining' => $this->faker->date('Y-m-d', '-2 years'),
            'dor' => $this->faker->date('Y-m-d', '+10 years'),
            'gpf_nps' => $this->faker->numerify('NPS##########'),
            'email' => $this->faker->unique()->safeEmail,
            'present_address' => $this->faker->address,
            'permanent_address' => $this->faker->address,
            'pincode' => $this->faker->numerify('7810##'),
            'district' => $this->faker->randomElement(['Kamrup', 'Kamrup Metropolitan', 'Jorhat', 'Dibrugarh', 'Cachar', 'Nagaon', 'Sonitpur']),
            'active' => '1',
            'ac_number' => $this->faker->bankAccountNumber,
            'ac_type' => 'Savings',
            'ac_name' => $fullName,
            'ac_bank' => 'State Bank of India',
            'ac_branch' => 'Dispur',
            'ac_ifsc' => 'SBIN0000001',
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'deleted_by' => null,
        ];
    }
}
