<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nip', 30)->nullable()->unique();
            $table->string('nuptk', 30)->nullable();
            $table->string('nik', 30)->nullable();
            $table->string('name');
            $table->string('title_prefix')->nullable();
            $table->string('title_suffix')->nullable();
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('education')->nullable();
            $table->string('major')->nullable();
            $table->string('employment_status')->default('PNS'); // PNS, PPPK, GTY, GTT
            $table->string('position')->nullable(); // Guru, Wali Kelas, Kepala Sekolah, Wakasek, etc.
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
