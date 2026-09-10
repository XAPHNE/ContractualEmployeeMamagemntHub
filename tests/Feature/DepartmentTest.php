<?php

namespace Tests\Feature;

use App\Models\Ddo;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_auto_generates_sequential_dept_id(): void
    {
        $dept1 = Department::create([
            'name' => 'Health Department',
            'code' => 'HEALTH',
        ]);

        $dept2 = Department::create([
            'name' => 'Education Department',
            'code' => 'EDU',
        ]);

        $this->assertEquals('1', $dept1->dept_id);
        $this->assertEquals('2', $dept2->dept_id);
    }

    public function test_department_preserves_custom_dept_id(): void
    {
        $dept = Department::create([
            'name' => 'Rural Development',
            'code' => 'P&RD',
            'dept_id' => '32',
        ]);

        $this->assertEquals('32', $dept->dept_id);
    }

    public function test_ddo_belongs_to_department(): void
    {
        $user = User::factory()->create();

        $dept = Department::create([
            'name' => 'Power Department',
            'code' => 'PWR',
            'dept_id' => '10',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $ddo = Ddo::factory()->create([
            'department_id' => $dept->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->assertNotNull($ddo->department);
        $this->assertEquals($dept->id, $ddo->department->id);
        $this->assertEquals('Power Department', $ddo->departmentName);
        $this->assertEquals('10', $ddo->department->dept_id);
        $this->assertTrue($dept->ddos->contains($ddo));
    }
}
