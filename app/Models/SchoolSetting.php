<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = [
        'school_name',
        'logo_path',
        'address',
        'phone',
        'email',
        'website'
    ];

    public static function get()
    {
        return self::first() ?? new self();
    }

    public function getLogoUrlAttribute()
    {
        if ($this->logo_path && file_exists(public_path($this->logo_path))) {
            return asset($this->logo_path);
        }
        return asset('admin_assets/images/brand/logo/logo_sekolah.png');
    }
}