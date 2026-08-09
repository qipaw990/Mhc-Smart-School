<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Header Rapor Siswa Semester
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('set null');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->integer('sick_count')->default(0);
            $table->integer('permit_count')->default(0);
            $table->integer('absent_count')->default(0);
            $table->text('homeroom_notes')->nullable();
            $table->enum('promotion_status', ['naik_kelas', 'tinggal_kelas', 'lulus', 'belum_lulus'])->default('naik_kelas');
            $table->enum('status', ['draft', 'published', 'locked'])->default('published');
            $table->integer('class_rank')->nullable();
            $table->timestamps();

            $table->unique(['academic_year_id', 'student_id', 'class_id']);
        });

        // 2. Rincian Nilai Mapel & Auto-Deskripsi Capaian TP
        Schema::create('report_card_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_card_id')->constrained('report_cards')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->onDelete('set null');
            $table->decimal('final_score', 5, 2)->default(0.00);
            $table->string('predicate', 5)->default('B'); // A, B, C, D
            $table->text('highest_competency_desc')->nullable(); // Menunjukkan penguasaan sangat baik dalam...
            $table->text('lowest_competency_desc')->nullable();  // Perlu bimbingan dan peningkatan dalam...
            $table->timestamps();
        });

        // 3. Ekstrakurikuler Rapor
        Schema::create('report_card_extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_card_id')->constrained('report_cards')->onDelete('cascade');
            $table->string('activity_name'); // e.g. "Pramuka Wajib", "Web Programming Club"
            $table->string('predicate', 20)->default('Sangat Baik');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 4. Projek Penguatan Profil Pelajar Pancasila (P5)
        Schema::create('p5_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('set null');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('theme'); // e.g. "Kebekerjaan", "Gaya Hidup Berkelanjutan"
            $table->string('title'); // e.g. "Membangun Portofolio Digital Software Developer"
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 5. Dimensi & Sub-Elemen Projek P5
        Schema::create('p5_project_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p5_project_id')->constrained('p5_projects')->onDelete('cascade');
            $table->string('dimension_name'); // e.g. "Mandiri", "Bernalar Kritis", "Kreatif", "Gotong Royong"
            $table->string('element');
            $table->string('sub_element');
            $table->string('target_phase', 10)->default('E');
            $table->timestamps();
        });

        // 6. Nilai Projek P5 Siswa (Skala MB, SB, BSH, SAB)
        Schema::create('p5_student_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p5_project_dimension_id')->constrained('p5_project_dimensions')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('score', ['MB', 'SB', 'BSH', 'SAB'])->default('BSH'); // Mulai Berkembang, Sedang Berkembang, Berkembang Sesuai Harapan, Sangat Berkembang
            $table->text('teacher_notes')->nullable();
            $table->timestamps();

            $table->unique(['p5_project_dimension_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p5_student_scores');
        Schema::dropIfExists('p5_project_dimensions');
        Schema::dropIfExists('p5_projects');
        Schema::dropIfExists('report_card_extracurriculars');
        Schema::dropIfExists('report_card_grades');
        Schema::dropIfExists('report_cards');
    }
};
