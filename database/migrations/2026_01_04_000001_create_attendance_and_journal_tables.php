<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Presensi Siswa & Guru
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('schedule_item_id')->nullable()->constrained('schedule_items')->onDelete('set null');
            $table->foreignId('student_id')->nullable()->constrained('students')->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->onDelete('set null'); // Who recorded / teacher attendance
            $table->date('date');
            $table->time('time');
            $table->enum('type', ['daily', 'subject_session'])->default('subject_session');
            $table->enum('method', ['qr_dynamic', 'qr_card', 'rfid', 'manual', 'gps'])->default('manual');
            $table->enum('status', ['H', 'S', 'I', 'A', 'T', 'D', 'P'])->default('H'); // Hadir, Sakit, Izin, Alpa, Terlambat, Dispensasi, PKL
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('device_info')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['date', 'student_id']);
            $table->index(['date', 'schedule_item_id']);
        });

        // 2. Dynamic QR Attendance Sessions (Anti-cheat rotating token)
        Schema::create('qr_attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_item_id')->constrained('schedule_items')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->integer('refresh_interval_sec')->default(15);
            $table->timestamps();
        });

        // 3. Jurnal Mengajar Digital Guru
        Schema::create('teaching_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('schedule_item_id')->nullable()->constrained('schedule_items')->onDelete('set null');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('learning_objective_id')->nullable()->constrained('learning_objectives')->onDelete('set null');
            $table->date('date');
            $table->tinyInteger('period_start');
            $table->tinyInteger('period_end');
            $table->text('topic_activity'); // Ringkasan materi & aktivitas pembelajaran
            $table->text('notes_challenges')->nullable(); // Hambatan / catatan kejadian khusus
            $table->string('photo_url')->nullable(); // Foto dokumentasi kelas
            $table->integer('student_present_count')->default(0);
            $table->integer('student_absent_count')->default(0);
            $table->enum('status', ['draft', 'submitted', 'verified'])->default('submitted');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_journals');
        Schema::dropIfExists('qr_attendance_sessions');
        Schema::dropIfExists('attendances');
    }
};
