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
        'tanggal',
        'jam_checkin_mulai',
        'jam_checkin_selesai',
        'jam_checkout_mulai',
        'jam_checkout_selesai',
        'latitude_checkin',
        'longitude_checkin',
        'radius_checkin',
        'latitude_checkout',
        'longitude_checkout',
        'radius_checkout',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_checkin_mulai' => 'datetime:H:i',
        'jam_checkin_selesai' => 'datetime:H:i',
        'jam_checkout_mulai' => 'datetime:H:i',
        'jam_checkout_selesai' => 'datetime:H:i',
        'latitude_checkin' => 'decimal:8',
        'longitude_checkin' => 'decimal:8',
        'latitude_checkout' => 'decimal:8',
        'longitude_checkout' => 'decimal:8',
        'radius_checkin' => 'integer',
        'radius_checkout' => 'integer',
    ];

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

    public function qrCode()
    {
        return $this->hasOne(QRCode::class, 'session_id');
    }

    /**
     * Check if checkin is currently active
     */
    public function isCheckinActive()
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = Carbon::now();
        $sessionDate = Carbon::parse($this->tanggal);

        if (!$now->isSameDay($sessionDate)) {
            return false;
        }

        $checkinMulai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $this->jam_checkin_mulai->format('H:i:s'));
        $checkinSelesai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $this->jam_checkin_selesai->format('H:i:s'));

        return $now->between($checkinMulai, $checkinSelesai);
    }

    /**
     * Check if checkout is currently active
     */
    public function isCheckoutActive()
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = Carbon::now();
        $sessionDate = Carbon::parse($this->tanggal);

        if (!$now->isSameDay($sessionDate)) {
            return false;
        }

        $checkoutMulai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $this->jam_checkout_mulai->format('H:i:s'));
        $checkoutSelesai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $this->jam_checkout_selesai->format('H:i:s'));

        return $now->between($checkoutMulai, $checkoutSelesai);
    }

    /**
     * Get current session phase (checkin/checkout/none)
     */
    public function getCurrentPhase()
    {
        if ($this->isCheckinActive()) {
            return 'checkin';
        }
        
        if ($this->isCheckoutActive()) {
            return 'checkout';
        }
        
        return 'none';
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

        $checkoutSelesai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $this->jam_checkout_selesai->format('H:i:s'));

        if ($now->isAfter($checkoutSelesai)) {
            return 'expired';
        }

        if ($this->isCheckinActive()) {
            return 'checkin_active';
        }

        if ($this->isCheckoutActive()) {
            return 'checkout_active';
        }

        $checkinMulai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $this->jam_checkin_mulai->format('H:i:s'));

        if ($now->isBefore($checkinMulai)) {
            return 'waiting';
        }

        return 'between_sessions';
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