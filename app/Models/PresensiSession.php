<?php
// app/Models/PresensiSession.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PresensiSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'kelas_id',
        'created_by',
        'qr_code',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'latitude',
        'longitude',
        'radius',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Generate QR Code otomatis saat create
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->qr_code)) {
                // Generate unique QR code
                $model->qr_code = Str::random(32);
            }
        });
    }

    /**
     * Relasi ke Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Relasi ke Guru yang membuat session
     */
    public function guru()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke Presensi (banyak siswa yang absen)
     */
    public function presensis()
    {
        return $this->hasMany(Presensi::class);
    }

    /**
     * Cek apakah session masih aktif
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Cek apakah session sudah expired (lewat jam selesai)
     */
    public function isExpired()
    {
        $now = Carbon::now();
        $sessionDateTime = Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . $this->jam_selesai);
        return $now->greaterThan($sessionDateTime);
    }

    /**
     * Cek apakah session bisa digunakan (aktif & belum expired)
     */
    public function isValid()
    {
        return $this->isActive() && !$this->isExpired();
    }

    /**
     * Get URL untuk QR Code
     */
    public function getQrCodeUrlAttribute()
    {
        return route('siswa.presensi.verify-form', ['code' => $this->qr_code]);
    }

    /**
     * Get formatted tanggal
     */
    public function getTanggalFormatAttribute()
    {
        return $this->tanggal->format('d/m/Y');
    }

    /**
     * Get formatted waktu
     */
    public function getWaktuFormatAttribute()
    {
        return $this->jam_mulai . ' - ' . $this->jam_selesai;
    }

    /**
     * Scope untuk session aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk session hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal', Carbon::today());
    }

    /**
     * Scope untuk session berdasarkan guru
     */
    public function scopeByGuru($query, $guruId)
    {
        return $query->where('created_by', $guruId);
    }
}