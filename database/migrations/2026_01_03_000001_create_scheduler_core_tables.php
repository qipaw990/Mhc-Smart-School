<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Time Slots (Jam Pelajaran 1 s/d 10 per Hari)
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])->default('Senin');
            $table->tinyInteger('period'); // 1, 2, 3... 10
            $table->time('start_time'); // e.g. 07:00
            $table->time('end_time'); // e.g. 07:45
            $table->boolean('is_break')->default(false); // Istirahat
            $table->string('label')->nullable(); // e.g. "Upacara", "Istirahat 1", "Jam 1"
            $table->timestamps();
        });

        // 2. Teacher Availabilities (Ketersediaan Hari & Jam Guru)
        Schema::create('teacher_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->tinyInteger('period');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // 3. Teaching Loads (Beban Mengajar: Guru -> Mapel -> Kelas -> JP)
        Schema::create('teaching_loads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('set null');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->integer('hours_per_week')->default(4); // JP per minggu
            $table->foreignId('preferred_room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Schedules (Header Versi Jadwal Sekolah)
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('set null');
            $table->string('name'); // e.g. "Jadwal Semester Ganjil 2026/2027 v1.0"
            $table->string('version', 20)->default('1.0');
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->decimal('optimization_score', 5, 2)->default(100.00); // e.g. 98.50%
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Schedule Items (Item Jadwal: Hari, Jam, Guru, Kelas, Mapel, Ruang)
        Schema::create('schedule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('time_slot_id')->constrained('time_slots')->onDelete('cascade');
            $table->foreignId('teaching_load_id')->nullable()->constrained('teaching_loads')->onDelete('set null');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->tinyInteger('period');
            $table->tinyInteger('consecutive_hours')->default(1);
            $table->timestamps();
        });

        // 6. Schedule Histories (Audit Perubahan & Regenerasi Jadwal)
        Schema::create('schedule_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // generated, regenerated, edited, published
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_histories');
        Schema::dropIfExists('schedule_items');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('teaching_loads');
        Schema::dropIfExists('teacher_availabilities');
        Schema::dropIfExists('time_slots');
    }
};
