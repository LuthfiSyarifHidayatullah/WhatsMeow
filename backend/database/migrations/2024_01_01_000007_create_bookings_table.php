<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('title'); // Nama kegiatan
            $table->string('booked_by'); // OPD/instansi yang booking
            $table->string('pic_name')->nullable(); // Penanggung jawab
            $table->string('pic_phone')->nullable(); // Kontak PJ
            $table->date('date'); // Tanggal kegiatan
            $table->time('start_time'); // Jam mulai
            $table->time('end_time'); // Jam selesai
            $table->string('location'); // Ruangan: "Media Center" atau "Podcast"
            $table->string('status')->default('confirmed'); // confirmed, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); // Petugas yang input
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
