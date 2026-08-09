<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bank Soal
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('learning_objective_id')->nullable()->constrained('learning_objectives')->onDelete('set null');
            $table->string('title');
            $table->enum('phase', ['E', 'F'])->default('E');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Butir Soal
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained('question_banks')->onDelete('cascade');
            $table->enum('type', ['pg', 'pgk', 'true_false', 'matching', 'short_answer', 'essay'])->default('pg');
            $table->enum('cognitive_level', ['lots', 'mots', 'hots'])->default('mots');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->longText('question_text');
            $table->string('media_url')->nullable();
            $table->text('code_snippet')->nullable(); // For vocational coding tests
            $table->decimal('score_weight', 5, 2)->default(10.00);
            $table->integer('order_number')->default(1);
            $table->text('explanation')->nullable();
            $table->timestamps();
        });

        // 3. Opsi Jawaban Soal
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->string('option_label', 10)->nullable(); // A, B, C, D, E
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->text('matching_pair')->nullable(); // For matching pairs
            $table->timestamps();
        });

        // 4. Pengaturan Ujian (Exams)
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('set null');
            $table->foreignId('question_bank_id')->constrained('question_banks')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->string('title');
            $table->string('token', 10)->default('SMART');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration_minutes')->default(60);
            $table->decimal('kktp_score', 5, 2)->default(75.00);
            $table->boolean('randomize_questions')->default(true);
            $table->boolean('randomize_options')->default(true);
            $table->tinyInteger('max_tab_switches')->default(3); // Anti-cheat tolerance
            $table->enum('status', ['draft', 'published', 'ongoing', 'finished'])->default('published');
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        // 5. Target Rombel Kelas Ujian
        Schema::create('exam_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->timestamps();
        });

        // 6. Sesi Ujian Siswa (Student Exam Attempt)
        Schema::create('student_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('submit_time')->nullable();
            $table->integer('duration_used_seconds')->default(0);
            $table->enum('status', ['in_progress', 'submitted', 'forced_submit', 'blocked'])->default('in_progress');
            $table->tinyInteger('tab_switch_count')->default(0);
            $table->decimal('total_score', 5, 2)->default(0.00);
            $table->boolean('is_passed')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
        });

        // 7. Lembar Jawaban Butir Siswa (Answers)
        Schema::create('student_exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_exam_id')->constrained('student_exams')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->json('answer_json')->nullable(); // Supports single option, multi checkboxes, text, or matching
            $table->boolean('is_correct')->default(false);
            $table->boolean('is_doubtful')->default(false); // Ragu-ragu
            $table->decimal('score_obtained', 5, 2)->default(0.00);
            $table->text('teacher_notes')->nullable();
            $table->timestamps();

            $table->unique(['student_exam_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_exam_answers');
        Schema::dropIfExists('student_exams');
        Schema::dropIfExists('exam_classes');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_banks');
    }
};
