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
        'waktu_presensi',
        'latitude',
        'longitude',
        'metode',
        'keterangan',
        'bukti_file',
        'notifikasi_terkirim',
    ];

    protected $casts = [
        'waktu_presensi' => 'datetime',
        'notifikasi_terkirim' => 'boolean',
    ];

    public function session()
    {
        return $this->belongsTo(PresensiSession::class, 'session_id');
    }

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}