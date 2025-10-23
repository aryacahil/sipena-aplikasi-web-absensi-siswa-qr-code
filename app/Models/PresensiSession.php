<?php

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

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isExpired()
    {
        $now = Carbon::now();
        $sessionDateTime = Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . $this->jam_selesai);
        return $now->greaterThan($sessionDateTime);
    }

    public function isValid()
    {
        return $this->isActive() && !$this->isExpired();
    }

    public function getQrCodeUrlAttribute()
    {
        return route('siswa.presensi.verify-form', ['code' => $this->qr_code]);
    }

    public function getTanggalFormatAttribute()
    {
        return $this->tanggal->format('d/m/Y');
    }

    public function getWaktuFormatAttribute()
    {
        return $this->jam_mulai . ' - ' . $this->jam_selesai;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('tanggal', Carbon::today());
    }

    public function scopeByGuru($query, $guruId)
    {
        return $query->where('created_by', $guruId);
    }
}