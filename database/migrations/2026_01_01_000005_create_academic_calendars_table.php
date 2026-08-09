<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('event_type', ['effective', 'holiday', 'exam', 'mpls', 'pkl', 'report', 'agenda'])->default('effective');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('color', 20)->default('#4e73df');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_calendars');
    }
};
