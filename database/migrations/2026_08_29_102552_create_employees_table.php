<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id');
            $table->string('full_Name');
            $table->string('first_Name');
            $table->string('middle_Name')->nullable();
            $table->string('last_Name');
            $table->string('type');
            $table->string('mobile');
            $table->string('employee_code');
            $table->string('pan');
            $table->string('gender');
            $table->string('dob');
            $table->string('designation');
            $table->string('grade');
            $table->string('pay_band')->nullable();
            $table->string('grade_pay')->nullable();
            $table->date('date_of_joining');
            $table->date('dor');
            $table->string('gpf_nps')->nullable();
            $table->string('email')->nullable();
            $table->string('present_address');
            $table->string('permanent_address');
            $table->string('pincode');
            $table->string('district');
            $table->string('active');
            $table->string('ac_number');
            $table->string('ac_type');
            $table->string('ac_name');
            $table->string('ac_bank');
            $table->string('ac_branch');
            $table->string('ac_ifsc');
            $table->foreignId('created_by')->constrained('users')->onupdate('cascade')->ondelete('restrict');
            $table->foreignId('updated_by')->constrained('users')->onupdate('cascade')->ondelete('restrict');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onupdate('cascade')->ondelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
