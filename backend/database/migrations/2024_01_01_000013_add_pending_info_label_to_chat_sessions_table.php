<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menyimpan label info terakhir yang ditampilkan ke pengunjung, agar
        // jawaban "Ya"/"Tidak" berikutnya diketahui merujuk info yang mana.
        // Dikosongkan setelah dijawab atau saat pengunjung berpindah menu.
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->string('pending_info_label')->nullable()->after('topic');
        });
    }

    public function down(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropColumn('pending_info_label');
        });
    }
};
