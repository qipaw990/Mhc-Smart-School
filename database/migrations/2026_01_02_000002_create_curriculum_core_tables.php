<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Capaian Pembelajaran (CP)
        Schema::create('learning_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
            $table->enum('phase', ['E', 'F'])->default('E');
            $table->string('code', 30); // e.g. CP-RPL-01
            $table->string('element'); // e.g. "Pemrograman Berorientasi Objek", "Basis Data Relasional"
            $table->text('description'); // Deskripsi resmi capaian pembelajaran
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Tujuan Pembelajaran (TP)
        Schema::create('learning_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_outcome_id')->constrained('learning_outcomes')->onDelete('cascade');
            $table->string('code', 30); // e.g. TP-01
            $table->integer('order_number')->default(1);
            $table->text('description'); // e.g. "Peserta didik mampu merancang struktur database relasional menggunakan MySQL..."
            $table->tinyInteger('semester_number')->default(1); // 1 or 2
            $table->integer('estimated_hours')->default(8); // JP
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. ATP (Alur Tujuan Pembelajaran) Builder Header
        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('major_id')->nullable()->constrained('majors')->onDelete('set null');
            $table->enum('phase', ['E', 'F'])->default('E');
            $table->tinyInteger('semester_number')->default(1);
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. ATP Timeline Items (Items per minggu / alokasi JP)
        Schema::create('learning_path_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained('learning_paths')->onDelete('cascade');
            $table->foreignId('learning_objective_id')->constrained('learning_objectives')->onDelete('cascade');
            $table->integer('sequence_order')->default(1);
            $table->integer('week_number')->default(1); // Minggu ke-1, 2, dst
            $table->integer('hour_allocation')->default(4); // Alokasi JP
            $table->string('topic');
            $table->text('assessment_plan')->nullable(); // Rencana asesmen
            $table->timestamps();
        });

        // 5. Materi Pembelajaran Terhubung TP
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_objective_id')->constrained('learning_objectives')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('video_url')->nullable();
            $table->string('external_link')->nullable();
            $table->integer('estimated_minutes')->default(90);
            $table->integer('sequence_order')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Modul Ajar Generator Kurikulum Merdeka
        Schema::create('teaching_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('learning_outcome_id')->constrained('learning_outcomes')->onDelete('cascade');
            $table->foreignId('learning_objective_id')->constrained('learning_objectives')->onDelete('cascade');
            $table->string('title');
            $table->enum('phase', ['E', 'F'])->default('E');
            $table->enum('grade_level', ['X', 'XI', 'XII'])->default('X');
            $table->integer('allocated_hours')->default(4);
            $table->string('learning_model')->default('Project Based Learning (PjBL)'); // PjBL, PBL, Discovery Learning
            $table->string('methods')->default('Diskusi, Praktik Lab, Demonstrasi, Presentasi');
            $table->string('target_students')->default('Siswa Reguler / Tipikal');
            $table->text('preliminary_activities')->nullable(); // Kegiatan Pendahuluan (15 mnt)
            $table->text('core_activities')->nullable(); // Kegiatan Inti (150 mnt)
            $table->text('closing_activities')->nullable(); // Kegiatan Penutup (15 mnt)
            $table->text('diagnostic_assessment')->nullable(); // Asesmen Awal
            $table->text('formative_assessment')->nullable(); // Asesmen Proses
            $table->text('summative_assessment')->nullable(); // Asesmen Akhir
            $table->text('remedial_plan')->nullable(); // Program Remedial
            $table->text('enrichment_plan')->nullable(); // Program Pengayaan
            $table->text('student_worksheet')->nullable(); // LKPD (Lembar Kerja Peserta Didik)
            $table->text('assessment_rubric')->nullable(); // Rubrik Penilaian
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_modules');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('learning_path_items');
        Schema::dropIfExists('learning_paths');
        Schema::dropIfExists('learning_objectives');
        Schema::dropIfExists('learning_outcomes');
    }
};
