<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Umpan balik kebermanfaatan sebuah item informasi (menu action 'info').
        // Terpisah dari rating kepuasan petugas (chat_sessions.satisfaction_rating).
        // Satu baris = satu jawaban Ya/Tidak dari pengunjung (boleh berulang).
        Schema::create('info_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('chat_session_id')->nullable()->constrained('chat_sessions')->nullOnDelete();

            // Label item info yang dinilai (mis. "Informasi Pengajuan").
            $table->string('info_label');

            // true = membantu (Ya), false = tidak membantu (Tidak).
            $table->boolean('is_helpful');

            $table->string('visitor_phone')->nullable();
            $table->timestamps();

            $table->index(['service_id', 'is_helpful']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_feedbacks');
    }
};
