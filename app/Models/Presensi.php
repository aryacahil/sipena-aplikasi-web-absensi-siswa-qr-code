<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'kelas_id',
        'siswa_id',
        'tanggal_presensi',
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

    public function getWaktuPresensiAttribute()
    {
        return $this->created_at;
    }

    public function session()
    {
        return $this->belongsTo(PresensiSession::class, 'session_id');
    }

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('tanggal_presensi', $date);
    }

    public function scopeByKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}