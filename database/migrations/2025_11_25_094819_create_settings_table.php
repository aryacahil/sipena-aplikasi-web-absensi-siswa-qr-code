<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            [
                'key' => 'fonnte_api_key',
                'value' => null,
                'type' => 'string',
                'description' => 'API Key dari Fonnte untuk mengirim WhatsApp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fonnte_device_id',
                'value' => null,
                'type' => 'string',
                'description' => 'Device ID WhatsApp yang terhubung di Fonnte (opsional)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fonnte_sender_number',
                'value' => null,
                'type' => 'string',
                'description' => 'Nomor WhatsApp pengirim (contoh: 628123456789)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fonnte_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Aktifkan/nonaktifkan notifikasi WhatsApp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fonnte_message_template',
                'value' => "Assalamualaikum Bapak/Ibu *{parent_name}*\n\nKami informasikan bahwa putra/putri Anda:\n\n*Nama:* {student_name}\n*Kelas:* {class_name}\n*Status:* {status}\n*Waktu:* {time}\n*Tanggal:* {date}\n\nTerima kasih atas perhatiannya.\n\n_Pesan otomatis dari Sistem Presensi Sekolah_",
                'type' => 'text',
                'description' => 'Template pesan WhatsApp. Gunakan: {parent_name}, {student_name}, {class_name}, {status}, {time}, {date}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};