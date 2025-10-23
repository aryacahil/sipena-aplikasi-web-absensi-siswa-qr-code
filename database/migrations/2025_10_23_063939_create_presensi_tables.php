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
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users'); 
            $table->string('qr_code')->unique(); 
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->decimal('latitude', 10, 8); 
            $table->decimal('longitude', 11, 8);
            $table->integer('radius')->default(200); 
            $table->enum('status', ['active', 'expired'])->default('active');
            $table->timestamps();
            
            $table->index(['qr_code', 'status']);
            $table->index(['tanggal', 'kelas_id']);
        });

        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('presensi_sessions')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('hadir');
            $table->time('waktu_presensi')->nullable();
            $table->decimal('latitude', 10, 8)->nullable(); 
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('metode', ['qr', 'manual'])->default('qr'); 
            $table->text('keterangan')->nullable(); 
            $table->string('bukti_file')->nullable(); 
            $table->boolean('notifikasi_terkirim')->default(false);
            $table->timestamps();
            
            $table->unique(['session_id', 'siswa_id']);
            $table->index(['siswa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensis');
        Schema::dropIfExists('presensi_sessions');
    }
};