<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Pelayanan KTP", "Perpajakan", "Kepegawaian"
            $table->string('code')->unique(); // e.g., "ktp", "pajak", "pegawai"
            $table->text('description')->nullable();
            $table->json('keywords')->nullable(); // Keywords untuk matching otomatis
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
