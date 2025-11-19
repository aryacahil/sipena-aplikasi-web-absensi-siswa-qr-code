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
        // ===============================
        // TABEL PRESENSI_SESSION
        // ===============================
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

        // ===============================
        // TABEL PRESENSIS (UPDATED - SESSION-FREE!)
        // ===============================
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            
            // ⚠️ PERUBAHAN PENTING: GANTI onDelete('cascade') JADI onDelete('set null')
            // SESSION ID - NULLABLE (untuk QR Code scan)
            $table->foreignId('session_id')
                  ->nullable()
                  ->constrained('presensi_sessions')
                  ->onDelete('set null') // ✅ UBAH DARI cascade JADI set null
                  ->comment('NULL jika presensi manual tanpa QR');
            
            // KELAS ID - WAJIB (untuk presensi manual)
            $table->foreignId('kelas_id')
                  ->constrained('kelas')
                  ->onDelete('cascade')
                  ->comment('Wajib diisi untuk semua jenis presensi');
            
            // SISWA ID
            $table->foreignId('siswa_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // TANGGAL PRESENSI - WAJIB
            $table->date('tanggal_presensi')
                  ->comment('Tanggal presensi (tidak bergantung session)');
            
            // WAKTU ABSEN (tetap pakai created_at sebagai fallback)
            $table->timestamp('waktu_absen')->nullable();
            
            // STATUS PRESENSI
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])
                  ->default('hadir');
            
            // KOORDINAT GPS
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // KETERANGAN
            $table->text('keterangan')->nullable();
            
            // METODE PRESENSI
            $table->enum('metode', ['qr', 'manual'])
                  ->default('manual')
                  ->comment('qr = via scan QR code, manual = input manual');
            
            // VALIDASI LOKASI
            $table->boolean('is_valid_location')->default(true);
            
            $table->timestamps();

            // ========================================
            // UNIQUE CONSTRAINT - CRITICAL!
            // ========================================
            // Siswa hanya bisa 1x presensi per tanggal per kelas
            $table->unique(
                ['kelas_id', 'siswa_id', 'tanggal_presensi'], 
                'unique_presensi_per_day'
            );

            // ========================================
            // INDEX untuk performa query
            // ========================================
            $table->index('siswa_id');
            $table->index('session_id'); // untuk QR scan
            $table->index('status');
            $table->index('waktu_absen');
            $table->index('metode');
            $table->index('tanggal_presensi'); // PENTING untuk filter tanggal!
            $table->index(['kelas_id', 'tanggal_presensi']); // composite index
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