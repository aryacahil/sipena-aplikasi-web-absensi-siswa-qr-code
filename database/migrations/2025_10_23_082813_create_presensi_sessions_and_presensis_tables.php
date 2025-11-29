<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi_sessions', function (Blueprint $table) {
            $table->id();
            
            // Relasi
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade')
                ->comment('Admin/Guru yang membuat sesi');
            
            // Tanggal
            $table->date('tanggal')->comment('Tanggal presensi');
            
            // Waktu Check-in
            $table->time('jam_checkin_mulai')->comment('Jam mulai check-in');
            $table->time('jam_checkin_selesai')->comment('Jam selesai check-in');
            
            // Waktu Check-out
            $table->time('jam_checkout_mulai')->comment('Jam mulai check-out');
            $table->time('jam_checkout_selesai')->comment('Jam selesai check-out');
            
            // Lokasi Check-in
            $table->decimal('latitude_checkin', 10, 8)->nullable()->comment('Latitude titik check-in');
            $table->decimal('longitude_checkin', 11, 8)->nullable()->comment('Longitude titik check-in');
            $table->integer('radius_checkin')->default(200)->comment('Radius check-in dalam meter');
            
            // Lokasi Check-out
            $table->decimal('latitude_checkout', 10, 8)->nullable()->comment('Latitude titik check-out');
            $table->decimal('longitude_checkout', 11, 8)->nullable()->comment('Longitude titik check-out');
            $table->integer('radius_checkout')->default(200)->comment('Radius check-out dalam meter');
            
            // Status Sesi
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active')
                ->comment('Status sesi presensi');
            
            // Keterangan
            $table->text('keterangan')->nullable();
            
            $table->timestamps();

            // Index untuk performa
            $table->index(['kelas_id', 'tanggal']);
            $table->index('status');
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_sessions');
    }
};