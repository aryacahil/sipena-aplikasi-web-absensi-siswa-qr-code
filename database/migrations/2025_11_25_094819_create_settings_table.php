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
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('key');
        });

        DB::table('settings')->insert([
            // Global WhatsApp notification toggle
            [
                'key' => 'fonnte_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Aktifkan notifikasi WhatsApp (Global)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Message templates (masih di settings karena global untuk semua device)
            [
                'key' => 'fonnte_message_template_checkin',
                'value' => "Assalamualaikum Bapak/Ibu\n\nKami informasikan bahwa:\n\n*Nama:* {student_name}\n*NIS:* {nis}\n*Kelas:* {class_name}\n*Status:* ✅ MASUK\n*Waktu:* {checkin_time}\n*Tanggal:* {date}\n\nTerima kasih.\n\n_Sistem Presensi Sekolah_",
                'type' => 'text',
                'description' => 'Template pesan check-in',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fonnte_message_template_checkout',
                'value' => "Assalamualaikum Bapak/Ibu\n\nKami informasikan bahwa:\n\n*Nama:* {student_name}\n*NIS:* {nis}\n*Kelas:* {class_name}\n*Status:* 🏠 PULANG\n*Check-in:* {checkin_time}\n*Check-out:* {checkout_time}\n*Tanggal:* {date}\n\nTerima kasih.\n\n_Sistem Presensi Sekolah_",
                'type' => 'text',
                'description' => 'Template pesan check-out',
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