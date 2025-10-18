<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'id'=>1,
            'name' => 'Admin',
            'email'=>'admin@admin.com',
            'password'=>bcrypt('password'),
            'role'=>'1' 
        ]);
        
        User::create([
            'id'=>2,
            'name' => 'Guru',
            'email'=>'guru@guru.com',
            'password'=>bcrypt('password'),
            'role'=>'0' 
        ]);
        
        User::create([
            'id'=>3,
            'name' => 'Siswa',
            'email'=>'siswa@siswa.com',
            'password'=>bcrypt('password'),
            'role'=>'2' 
        ]);
    }
}