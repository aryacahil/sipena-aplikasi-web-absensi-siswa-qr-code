<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QRCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'qr_codes';

    protected $fillable = [
        'session_id',
        'qr_code_checkin',
        'qr_code_checkout',
        'status',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(PresensiSession::class, 'session_id');
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class, 'qr_code_id');
    }
}