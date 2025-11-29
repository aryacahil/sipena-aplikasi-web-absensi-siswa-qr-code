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

    // Relasi dengan jurusan
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    // Relasi dengan wali kelas
    public function waliKelas()
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    // FIXED: Relasi dengan siswa - Hapus filter role
    public function siswa()
    {
        return $this->hasMany(User::class, 'kelas_id')
                    ->where('role', 2); // 2 adalah raw value untuk siswa
    }

    // Accessor untuk mendapatkan jumlah siswa
    public function getSiswaCountAttribute()
    {
        return $this->siswa()->count();
    }

    // Accessor untuk mendapatkan nama lengkap kelas
    public function getNamaLengkapAttribute()
    {
        return $this->nama_kelas . ' - Tingkat ' . $this->tingkat;
    }
}