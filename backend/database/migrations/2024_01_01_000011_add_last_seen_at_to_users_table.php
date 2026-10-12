<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jejak kehadiran terakhir petugas/admin. Diperbarui oleh heartbeat
        // dari dashboard. Dipakai untuk menentukan petugas benar-benar aktif
        // (bukan sekadar is_online yang tertinggal 'true' karena tutup browser
        // tanpa logout).
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('is_available');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_seen_at');
        });
    }
};
