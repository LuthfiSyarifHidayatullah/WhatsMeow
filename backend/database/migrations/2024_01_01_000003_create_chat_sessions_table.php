<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique(); // UUID
            $table->string('visitor_phone'); // nomor WA pengunjung
            $table->string('visitor_name')->nullable();
            $table->string('chat_jid'); // WhatsApp JID
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('bot'); // bot, waiting, active, resolved, abandoned
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->text('topic')->nullable(); // Topik/masalah
            $table->integer('satisfaction_rating')->nullable(); // 1-5
            $table->text('satisfaction_feedback')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};
