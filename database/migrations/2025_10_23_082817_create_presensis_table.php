<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            
            // RELASI
            $table->foreignId('session_id')
                ->nullable()
                ->constrained('presensi_sessions')
                ->onDelete('set null')
                ->comment('Sesi presensi (NULL jika manual)');
            
            $table->foreignId('qr_code_id')
                ->nullable()
                ->constrained('qr_codes')
                ->onDelete('set null')
                ->comment('QR Code yang digunakan (NULL jika manual atau QR sudah dihapus)');
            
            $table->foreignId('kelas_id')
                ->constrained('kelas')
                ->onDelete('cascade')
                ->comment('Kelas siswa');
            
            $table->foreignId('siswa_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Siswa');
            
            // TANGGAL PRESENSI
            $table->date('tanggal_presensi')
                ->comment('Tanggal presensi');
            
            // WAKTU CHECK-IN & CHECK-OUT
            $table->timestamp('waktu_checkin')->nullable()
                ->comment('Waktu scan QR check-in atau input manual');
            
            $table->timestamp('waktu_checkout')->nullable()
                ->comment('Waktu scan QR check-out');
            
            // STATUS PRESENSI
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])
                ->default('hadir');
            
            // KOORDINAT GPS CHECK-IN & CHECK-OUT
            $table->decimal('latitude_checkin', 10, 8)->nullable();
            $table->decimal('longitude_checkin', 11, 8)->nullable();
            $table->decimal('latitude_checkout', 10, 8)->nullable();
            $table->decimal('longitude_checkout', 11, 8)->nullable();
            
            // VALIDASI LOKASI
            $table->boolean('is_valid_location_checkin')->default(true);
            $table->boolean('is_valid_location_checkout')->default(true);
            
            // KETERANGAN
            $table->text('keterangan_checkin')->nullable()
                ->comment('Keterangan check-in (jarak, accuracy, dll)');
            
            $table->text('keterangan_checkout')->nullable()
                ->comment('Keterangan check-out (jarak, accuracy, dll)');
            
            // METODE PRESENSI
            $table->enum('metode', ['qr', 'manual'])
                ->default('manual')
                ->comment('qr = via scan QR code, manual = input manual');
            
            // BUKTI FILE
            $table->string('bukti_file')->nullable()
                ->comment('Path file bukti (surat izin/sakit)');
            
            // NOTIFIKASI WHATSAPP
            $table->boolean('notifikasi_checkin_terkirim')->default(false);
            $table->boolean('notifikasi_checkout_terkirim')->default(false);
            
            $table->timestamps();

            // UNIQUE CONSTRAINT
            $table->unique(
                ['kelas_id', 'siswa_id', 'tanggal_presensi'], 
                'unique_siswa_presensi_per_day'
            );

            // INDEX
            $table->index('siswa_id');
            $table->index('session_id');
            $table->index('qr_code_id');
            $table->index('kelas_id');
            $table->index('status');
            $table->index('metode');
            $table->index('tanggal_presensi');
            $table->index(['kelas_id', 'tanggal_presensi']);
            $table->index(['siswa_id', 'tanggal_presensi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};