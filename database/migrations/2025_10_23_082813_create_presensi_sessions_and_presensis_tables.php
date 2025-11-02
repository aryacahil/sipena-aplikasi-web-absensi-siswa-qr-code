<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabel untuk menyimpan sesi presensi 
        Schema::create('presensi_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('qr_code', 32)->unique();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('radius')->default(200)->comment('Radius dalam meter');
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['kelas_id', 'tanggal']);
            $table->index('qr_code');
            $table->index('status');
        });

        // Tabel untuk menyimpan data presensi siswa
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('presensi_sessions')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('waktu_absen')->nullable();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('hadir');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('tipe_absen', ['qr', 'manual'])->default('qr');
            $table->boolean('is_valid_location')->default(true);
            $table->timestamps();
            
            // Unique constraint - siswa hanya bisa absen 1x per session
            $table->unique(['session_id', 'siswa_id']);
            
            // Index untuk performa
            $table->index('siswa_id');
            $table->index('status');
            $table->index('waktu_absen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
        Schema::dropIfExists('presensi_sessions');
    }
};