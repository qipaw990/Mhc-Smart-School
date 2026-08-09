<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('code', 30);
            $table->string('name');
            $table->enum('type', ['classroom', 'lab', 'workshop', 'hall', 'library'])->default('classroom');
            $table->integer('capacity')->default(36);
            $table->string('location')->nullable();
            $table->enum('status', ['active', 'maintenance'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
