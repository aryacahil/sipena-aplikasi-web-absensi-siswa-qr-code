<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->qr_code)) {
                $model->qr_code = Str::random(32);
            }
        });
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);{{  }}
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class, 'session_id');
    }

    public function isActive()
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();
        $sessionDate = $this->tanggal->format('Y-m-d');
        $today = $now->format('Y-m-d');

        if ($sessionDate !== $today) {
            return false;
        }

        $startTime = $this->tanggal->format('Y-m-d') . ' ' . $this->jam_mulai->format('H:i:s');
        $endTime = $this->tanggal->format('Y-m-d') . ' ' . $this->jam_selesai->format('H:i:s');

        return $now->between($startTime, $endTime);
    }

    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $earthRadius * $angle;
    }
}