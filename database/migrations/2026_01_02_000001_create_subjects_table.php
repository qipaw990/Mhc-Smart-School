<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('major_id')->nullable()->constrained('majors')->onDelete('set null'); // Null for general subjects
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->enum('group', ['A_general', 'B_vocational', 'C_concentration', 'mulok', 'p5'])->default('B_vocational');
            $table->enum('phase', ['E', 'F'])->default('E'); // E = Kelas X, F = Kelas XI & XII
            $table->enum('type', ['theory', 'practice', 'theory_practice'])->default('theory_practice');
            $table->integer('hours_per_week')->default(4); // JP per minggu
            $table->integer('total_hours')->default(72); // Total JP per semester
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
