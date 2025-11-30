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
        // Buat tabel fonnte_devices
        Schema::create('fonnte_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama device untuk identifikasi');
            $table->string('api_key')->unique()->comment('API Key dari Fonnte');
            $table->string('phone_number')->comment('Nomor WhatsApp yang terhubung');
            $table->string('device_id')->nullable()->comment('Device ID dari Fonnte');
            $table->boolean('is_active')->default(true)->comment('Status aktif/nonaktif');
            $table->integer('priority')->default(1)->comment('Priority untuk rotasi (1=highest)');
            $table->integer('sent_count')->default(0)->comment('Total pesan terkirim');
            $table->timestamp('last_used_at')->nullable()->comment('Terakhir digunakan');
            $table->timestamp('last_checked_at')->nullable()->comment('Terakhir di-check statusnya');
            $table->string('status')->default('unknown')->comment('connected/disconnected/error/unknown');
            $table->text('status_message')->nullable()->comment('Detail status/error message');
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['is_active', 'priority']);
            $table->index('sent_count');
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fonnte_devices');
    }
};