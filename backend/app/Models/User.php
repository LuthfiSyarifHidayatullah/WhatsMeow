<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'service_id',
        'is_online',
        'is_available',
        'last_seen_at',
        'max_concurrent_chats',
        'current_chat_count',
        'avatar',
    ];

    /**
     * Ambang (menit) sejak last_seen_at agar petugas dianggap benar-benar
     * hadir. Heartbeat dashboard berjalan tiap ~30 detik, jadi 3 menit memberi
     * toleransi untuk jeda jaringan tanpa membuat petugas aktif salah dianggap
     * offline.
     */
    public const PRESENCE_FRESHNESS_MINUTES = 3;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_online' => 'boolean',
        'is_available' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignedSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class, 'officer_id');
    }

    public function activeSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class, 'officer_id')
            ->where('status', 'active');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_user_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isOfficer(): bool
    {
        return $this->role === 'officer';
    }

    public function canAcceptChat(): bool
    {
        return $this->is_online
            && $this->isPresent()
            && $this->is_available
            && $this->current_chat_count < $this->max_concurrent_chats;
    }

    /**
     * Apakah kehadiran terakhir masih segar (dalam ambang PRESENCE_FRESHNESS).
     */
    public function isPresent(): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->gte(now()->subMinutes(self::PRESENCE_FRESHNESS_MINUTES));
    }

    /**
     * Scope: hanya pengguna yang benar-benar hadir (is_online = true DAN
     * last_seen_at masih dalam ambang kesegaran).
     */
    public function scopePresent($query)
    {
        return $query->where('is_online', true)
            ->where('last_seen_at', '>=', now()->subMinutes(self::PRESENCE_FRESHNESS_MINUTES));
    }
}
