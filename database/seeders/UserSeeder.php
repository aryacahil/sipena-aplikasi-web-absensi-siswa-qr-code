<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => '1',
            'kelas_id' => null,
            'status' => 'active',
        ]);
        
        // Guru
        User::create([
            'name' => 'Guru',
            'email' => 'guru@guru.com',
            'password' => Hash::make('password'),
            'role' => '0',
            'kelas_id' => null,
            'status' => 'active',
        ]);
        
        // Siswa 
        User::create([
            'name' => 'Siswa',
            'nis' => '233307037',
            'password' => Hash::make('password'),
            'role' => '2',
            'kelas_id' => null,
            'status' => 'active',
        ]);
    }
}