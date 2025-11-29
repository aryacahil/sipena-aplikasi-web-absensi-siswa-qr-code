<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'nis',           // TAMBAHKAN INI
        'password',
        'role',
        'kelas_id',
        'parent_phone',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the role attribute as string
     * Ini akan mengubah 0,1,2 menjadi 'guru','admin','siswa' saat diakses
     */
    protected function role(): Attribute
    {
        return new Attribute(
            get: fn ($value) => ["guru", "admin", "siswa"][$value],
        );
    }

    /**
     * Relasi ke Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Get raw role value (0, 1, atau 2) tanpa melalui accessor
     * Method ini penting untuk mendapatkan nilai asli role dari database
     */
    public function getRawOriginal($key = null, $default = null)
    {
        if ($key === 'role') {
            return $this->attributes['role'] ?? $default;
        }
        return parent::getRawOriginal($key, $default);
    }

    /**
     * Scope untuk filter berdasarkan raw role value
     */
    public function scopeWhereRawRole($query, $roleValue)
    {
        return $query->whereRaw('role = ?', [$roleValue]);
    }
}