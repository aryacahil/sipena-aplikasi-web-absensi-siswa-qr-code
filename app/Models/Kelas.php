<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $fillable = [
        'jurusan_id',
        'nama_kelas',
        'tingkat',
        'kode_kelas',
        'wali_kelas_id',
    ];

    // Relasi ke Jurusan
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    // Relasi ke Wali Kelas (User dengan role guru)
    public function waliKelas()
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    // Relasi ke Siswa (Users yang memiliki kelas_id ini)
    public function siswa()
    {
        return $this->hasMany(User::class, 'kelas_id');
    }
}