<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Presensi Session
            $table->foreignId('session_id')
                ->constrained('presensi_sessions')
                ->onDelete('cascade')
                ->comment('Sesi presensi terkait');
            
            // QR Code untuk Check-in dan Check-out
            $table->string('qr_code_checkin', 32)->unique()
                ->comment('QR Code untuk check-in');
            
            $table->string('qr_code_checkout', 32)->unique()
                ->comment('QR Code untuk check-out');
            
            // Status QR Code (bisa expired tapi sesi tetap active)
            $table->enum('status', ['active', 'expired', 'deleted'])->default('active')
                ->comment('Status QR Code (terpisah dari status sesi)');
            
            // Soft Delete (untuk history)
            $table->softDeletes()->comment('Soft delete untuk history QR Code');
            
            $table->timestamps();

            // Index untuk performa
            $table->index('session_id');
            $table->index('qr_code_checkin');
            $table->index('qr_code_checkout');
            $table->index('status');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};