<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            // Menyimpan OPD yang sedang dipilih visitor pada navigasi menu 2-tingkat
            // (OPD dipilih dulu, baru layanan). Nullable karena hanya relevan saat mode bot.
            $table->foreignId('current_opd_id')->nullable()->after('service_id')->constrained('opds')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropForeign(['current_opd_id']);
            $table->dropColumn('current_opd_id');
        });
    }
};
