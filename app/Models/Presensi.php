<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'qr_code_id',
        'kelas_id',
        'siswa_id',
        'tanggal_presensi',
        'waktu_checkin',
        'waktu_checkout',
        'status',
        'latitude_checkin',
        'longitude_checkin',
        'latitude_checkout',
        'longitude_checkout',
        'is_valid_location_checkin',
        'is_valid_location_checkout',
        'keterangan_checkin',
        'keterangan_checkout',
        'metode',
        'bukti_file',
        'notifikasi_checkin_terkirim',
        'notifikasi_checkout_terkirim',
    ];

    protected $casts = [
        'notifikasi_checkin_terkirim' => 'boolean',
        'notifikasi_checkout_terkirim' => 'boolean',
        'is_valid_location_checkin' => 'boolean',
        'is_valid_location_checkout' => 'boolean',
        'tanggal_presensi' => 'date',
        'waktu_checkin' => 'datetime',
        'waktu_checkout' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($presensi) {
            if ($presensi->metode === 'manual') {
                if ($presensi->status === 'hadir') {
                    $presensi->waktu_checkin = \Carbon\Carbon::parse($presensi->tanggal_presensi)
                        ->setTime(7, 0, 0);
                    
                    $presensi->waktu_checkout = \Carbon\Carbon::parse($presensi->tanggal_presensi)
                        ->setTime(15, 0, 0);
                } else {
                    $presensi->waktu_checkin = null;
                    $presensi->waktu_checkout = null;
                }
            }
        });

        static::updating(function ($presensi) {
            if ($presensi->metode === 'manual') {
                if ($presensi->status === 'hadir') {
                    if (empty($presensi->waktu_checkin)) {
                        $presensi->waktu_checkin = \Carbon\Carbon::parse($presensi->tanggal_presensi)
                            ->setTime(7, 0, 0);
                    }
                    
                    if (empty($presensi->waktu_checkout)) {
                        $presensi->waktu_checkout = \Carbon\Carbon::parse($presensi->tanggal_presensi)
                            ->setTime(15, 0, 0);
                    }
                } else {
                    $presensi->waktu_checkin = null;
                    $presensi->waktu_checkout = null;
                }
            }
        });
    }
    
    public function getWaktuPresensiAttribute()
    {
        return $this->waktu_checkin ?? $this->created_at;
    }

    public function hasCheckedIn()
    {
        return !is_null($this->waktu_checkin);
    }

    public function hasCheckedOut()
    {
        return !is_null($this->waktu_checkout);
    }

    public function isComplete()
    {
        return $this->hasCheckedIn() && $this->hasCheckedOut();
    }

    public function session()
    {
        return $this->belongsTo(PresensiSession::class, 'session_id');
    }

    public function qrCode()
    {
        return $this->belongsTo(QRCode::class, 'qr_code_id');
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

    public function scopeCheckedIn($query)
    {
        return $query->whereNotNull('waktu_checkin');
    }

    public function scopeCheckedOut($query)
    {
        return $query->whereNotNull('waktu_checkout');
    }

    public function scopeIncomplete($query)
    {
        return $query->whereNotNull('waktu_checkin')
                     ->whereNull('waktu_checkout');
    }
}