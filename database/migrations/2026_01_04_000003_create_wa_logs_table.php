<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('recipient_name')->nullable();
            $table->text('message');
            $table->enum('type', ['single', 'bulk', 'attendance'])->default('attendance');
            $table->enum('status', ['success', 'failed', 'pending'])->default('success');
            $table->text('response_info')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_logs');
    }
};
