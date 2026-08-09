<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('current_class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('major_id')->nullable()->constrained('majors')->onDelete('set null');
            $table->string('nis', 20)->nullable()->unique();
            $table->string('nisn', 20)->nullable()->unique();
            $table->string('nik', 30)->nullable();
            $table->string('name');
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('religion')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('photo')->nullable();
            $table->year('entry_year')->default(2026);
            $table->enum('status', ['active', 'graduated', 'transferred', 'dropped_out', 'deceased'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('father_name')->nullable();
            $table->string('father_nik')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_nik')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('student_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('parents')->onDelete('cascade');
            $table->enum('relationship', ['father', 'mother', 'guardian'])->default('father');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_parents');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('students');
    }
};
