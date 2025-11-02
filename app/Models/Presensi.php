<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'siswa_id',
        'status',
        'latitude',
        'longitude',
        'metode',  
        'keterangan',
        'bukti_file',
        'notifikasi_terkirim',
    ];

    protected $casts = [
        'notifikasi_terkirim' => 'boolean',
    ];

    // Accessor untuk waktu_presensi (menggunakan created_at)
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
}