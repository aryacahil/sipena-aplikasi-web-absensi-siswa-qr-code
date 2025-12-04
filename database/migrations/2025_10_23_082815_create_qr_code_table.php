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
            
            $table->foreignId('session_id')
                ->constrained('presensi_sessions')
                ->onDelete('cascade')
                ->comment('Sesi presensi terkait');
            
            $table->string('qr_code_checkin', 32)->unique()
                ->comment('QR Code untuk check-in');
            
            $table->string('qr_code_checkout', 32)->unique()
                ->comment('QR Code untuk check-out');
            
            $table->enum('status', ['active', 'expired', 'deleted'])->default('active')
                ->comment('Status QR Code (terpisah dari status sesi)');
            
            $table->softDeletes()->comment('Soft delete untuk history QR Code');
            
            $table->timestamps();

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