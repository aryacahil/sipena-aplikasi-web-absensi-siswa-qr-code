<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',        // NULLABLE
        'kelas_id',          // WAJIB - untuk presensi manual
        'siswa_id',
        'tanggal_presensi',  // WAJIB
        'status',
        'latitude',
        'longitude',
        'metode',  
        'keterangan',
        'bukti_file',
        'notifikasi_terkirim',
        'is_valid_location',
    ];

    protected $casts = [
        'notifikasi_terkirim' => 'boolean',
        'is_valid_location' => 'boolean',
        'tanggal_presensi' => 'date',
    ];

    // Accessor untuk waktu_presensi (menggunakan created_at)
    public function getWaktuPresensiAttribute()
    {
        return $this->created_at;
    }

    /**
     * Relasi ke PresensiSession (NULLABLE)
     */
    public function session()
    {
        return $this->belongsTo(PresensiSession::class, 'session_id');
    }

    /**
     * Relasi ke User (Siswa)
     */
    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    /**
     * ⭐ MISSING RELATION - INI YANG MENYEBABKAN ERROR!
     * Relasi ke Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('tanggal_presensi', $date);
    }

    /**
     * Scope untuk filter berdasarkan kelas
     */
    public function scopeByKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}