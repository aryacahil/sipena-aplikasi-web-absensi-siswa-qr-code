<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'presensi_session_id',
        'siswa_id',
        'waktu_absen',
        'status',
        'latitude',
        'longitude',
        'keterangan',
        'tipe_absen',
        'is_valid_location',
    ];

    protected $casts = [
        'waktu_absen' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_valid_location' => 'boolean',
    ];

    public function session()
    {
        return $this->belongsTo(PresensiSession::class, 'presensi_session_id');
    }

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    /**
     * Hitung jarak antara 2 koordinat GPS (dalam meter)
     * Menggunakan Haversine formula
     * 
     * @param float $lat1 Latitude titik 1
     * @param float $lon1 Longitude titik 1
     * @param float $lat2 Latitude titik 2
     * @param float $lon2 Longitude titik 2
     * @return float Jarak dalam meter
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    public function getStatusBadgeAttribute()
    {
        return [
            'hadir' => 'success',
            'izin' => 'warning',
            'sakit' => 'info',
            'alpha' => 'danger',
        ][$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        return [
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpha' => 'Alpha',
        ][$this->status] ?? 'Unknown';
    }

    public function getTipeAbsenTextAttribute()
    {
        return $this->tipe_absen === 'qr' ? 'QR Code' : 'Manual';
    }

    public function getWaktuAbsenFormatAttribute()
    {
        return $this->waktu_absen->format('d/m/Y H:i:s');
    }

    public function scopeHadir($query)
    {
        return $query->where('status', 'hadir');
    }

    public function scopeIzin($query)
    {
        return $query->where('status', 'izin');
    }

    public function scopeSakit($query)
    {
        return $query->where('status', 'sakit');
    }

    public function scopeAlpha($query)
    {
        return $query->where('status', 'alpha');
    }

    public function scopeQrCode($query)
    {
        return $query->where('tipe_absen', 'qr');
    }

    public function scopeManual($query)
    {
        return $query->where('tipe_absen', 'manual');
    }

    public function scopeBySiswa($query, $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    public function scopeBySession($query, $sessionId)
    {
        return $query->where('presensi_session_id', $sessionId);
    }

    /**
     * Validasi lokasi GPS
     * 
     * @param float $sessionLat Latitude session
     * @param float $sessionLon Longitude session
     * @param int $radius Radius dalam meter
     * @return bool
     */
    public function validateLocation($sessionLat, $sessionLon, $radius)
    {
        if (!$this->latitude || !$this->longitude) {
            return false;
        }

        $distance = self::calculateDistance(
            $sessionLat,
            $sessionLon,
            $this->latitude,
            $this->longitude
        );

        return $distance <= $radius;
    }
}