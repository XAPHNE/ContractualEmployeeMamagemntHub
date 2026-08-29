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
        Schema::create('ddos', function (Blueprint $table) {
            $table->id();
            $table->string('ddoId');
            $table->string('ddoName');
            $table->string('pan');
            $table->string('departmentName');
            $table->string('directorate')->nullable();
            $table->string('postName');
            $table->string('officeName');
            $table->string('officeAddress');
            $table->string('mobileNumber');
            $table->string('treasuryName')->nullable();
            $table->string('treasuryCode')->nullable();
            $table->string('email')->nullable();
            $table->string('districtName');
            $table->foreignId('created_by')->constrained('users')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('updated_by')->constrained('users')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ddos');
    }
};
