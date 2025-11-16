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
        'tanggal' => 'date',
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius' => 'integer',
    ];

    // Auto-generate QR code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->qr_code)) {
                $model->qr_code = Str::random(32);
            }
        });
    }

    // Relationships
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class, 'session_id');
    }

    /**
     * Check if QR Code session is currently active
     */
    public function isActive()
    {
        if ($this->status === 'expired' || $this->status === 'inactive') {
            return false;
        }

        $now = Carbon::now();
        $sessionDate = Carbon::parse($this->tanggal);

        // Check date
        if (!$now->isSameDay($sessionDate)) {
            return false;
        }

        // Combine date with time
        $jamMulai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $this->jam_mulai->format('H:i:s'));
        $jamSelesai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $this->jam_selesai->format('H:i:s'));

        return $now->between($jamMulai, $jamSelesai);
    }

    /**
     * Get status text based on time
     */
    public function getStatusText()
    {
        if ($this->status === 'expired' || $this->status === 'inactive') {
            return 'expired';
        }

        $now = Carbon::now();
        $sessionDate = Carbon::parse($this->tanggal);

        if ($now->isAfter($sessionDate->endOfDay())) {
            return 'expired';
        }

        $jamMulai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $this->jam_mulai->format('H:i:s'));
        $jamSelesai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $this->jam_selesai->format('H:i:s'));

        if ($now->isAfter($jamSelesai)) {
            return 'expired';
        }

        if ($now->between($jamMulai, $jamSelesai)) {
            return 'active';
        }

        if ($now->isBefore($jamMulai)) {
            return 'waiting';
        }

        return 'expired';
    }

    /**
     * Auto-set expired if needed
     */
    public function updateStatusIfExpired()
    {
        if ($this->getStatusText() === 'expired' && $this->status === 'active') {
            $this->update(['status' => 'expired']);
            return true;
        }

        return false;
    }

    /**
     * Haversine distance in meters
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo   = deg2rad($lat2);
        $lonTo   = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $earthRadius * $angle;
    }
}
