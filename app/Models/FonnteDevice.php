<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FonnteDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'api_key',
        'phone_number',
        'device_id',
        'is_active',
        'priority',
        'sent_count',
        'last_used_at',
        'last_checked_at',
        'status',
        'status_message',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'sent_count' => 'integer',
        'last_used_at' => 'datetime',
        'last_checked_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->active()
                    ->where('status', '!=', 'error');
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc')
                    ->orderBy('sent_count', 'asc')
                    ->orderBy('last_used_at', 'asc');
    }

    public function incrementSentCount(): void
    {
        $this->increment('sent_count');
        $this->update(['last_used_at' => now()]);
    }

    public function updateStatus(string $status, ?string $message = null): void
    {
        $this->update([
            'status' => $status,
            'status_message' => $message,
            'last_checked_at' => now(),
        ]);
    }

    public function getFormattedPhoneAttribute(): string
    {
        $phone = $this->phone_number;
        
        if (substr($phone, 0, 2) === '62') {
            return '0' . substr($phone, 2);
        }
        
        return $phone;
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'connected' => 'success',
            'disconnected' => 'warning',
            'error' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'connected' => 'Terhubung',
            'disconnected' => 'Terputus',
            'error' => 'Error',
            default => 'Unknown',
        };
    }
}