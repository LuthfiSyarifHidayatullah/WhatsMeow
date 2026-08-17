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
            $table->string('title');
            $table->string('booked_by');
            $table->string('pic_name')->nullable();
            $table->string('pic_phone')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location'); // "Media Center" atau "Podcast"
            $table->string('status')->default('confirmed'); // confirmed, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
