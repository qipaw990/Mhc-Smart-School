<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Asesmen Kurikulum Merdeka (Formatif, Sumatif TP, Sumatif SAS/PAT)
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('set null');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('learning_objective_id')->nullable()->constrained('learning_objectives')->onDelete('set null');
            $table->string('title'); // e.g. "Formatif 1: Flowchart & Pseudocode", "Sumatif Lingkup Materi TP 1-2"
            $table->enum('type', ['diagnostic', 'formative', 'summative_tp', 'summative_semester'])->default('formative');
            $table->decimal('kktp_score', 5, 2)->default(75.00); // Kriteria Ketercapaian TP Minimal
            $table->decimal('max_score', 5, 2)->default(100.00);
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Nilai Siswa (Assessment Scores & Remedial Tracker)
        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->decimal('score', 5, 2)->default(0.00);
            $table->boolean('is_remedial')->default(false);
            $table->decimal('remedial_score', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->default(0.00);
            $table->enum('achievement_status', ['achieved', 'not_achieved'])->default('achieved');
            $table->text('teacher_notes')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_scores');
        Schema::dropIfExists('assessments');
    }
};
