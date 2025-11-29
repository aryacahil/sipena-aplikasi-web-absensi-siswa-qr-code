<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('nis', 20)->nullable()->unique()->comment('Nomor Induk Siswa (untuk role siswa)');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->tinyInteger('role')->default(2)->comment('0=guru, 1=admin, 2=siswa');
            $table->string('parent_phone')->nullable()->comment('No. Telepon Orang Tua (untuk siswa)');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->rememberToken();
            $table->timestamps();
            $table->index('role');
            $table->index('status');
            $table->index('email');
            $table->index('nis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};