<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sub-menu / pilihan yang muncul setelah visitor memilih sebuah layanan.
        // Sebelumnya di-hard-code di ChatbotService::$serviceMenus; sekarang
        // dapat dikelola dari dashboard sehingga layanan baru tidak perlu
        // mengubah kode.
        Schema::create('service_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();

            // Nomor pilihan yang ditampilkan ke visitor (1, 2, 3, ...)
            $table->unsignedInteger('position');

            // Label pilihan, mis. "Informasi Pengajuan", "Hubungi Petugas"
            $table->string('label');

            // Tipe aksi:
            //   info                    -> tampilkan teks informasi
            //   schedule                -> tampilkan jadwal dari tabel bookings
            //   formulir_then_escalate  -> tampilkan link/teks formulir lalu ke petugas
            //   escalate                -> langsung hubungi petugas
            $table->string('action')->default('info');

            // Untuk action info/formulir: teks respons diisi langsung di sini.
            // (Dulu diambil dari bot_responses via response_key; kini dashboard
            //  mengisi teksnya langsung agar lebih sederhana.)
            $table->text('response_text')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['service_id', 'position']);
            $table->index(['service_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_menu_items');
    }
};
