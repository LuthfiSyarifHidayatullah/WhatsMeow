<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Template respons bot yang bisa di-manage dari dashboard
        Schema::create('bot_responses', function (Blueprint $table) {
            $table->id();
            $table->string('trigger_keyword'); // Keyword yang memicu respons
            $table->text('response_text'); // Teks respons bot
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('match_type')->default('contains'); // exact, contains, regex
            $table->integer('priority')->default(0); // Prioritas jika ada multiple match
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_responses');
    }
};
