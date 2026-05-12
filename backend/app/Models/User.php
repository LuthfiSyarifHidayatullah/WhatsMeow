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
        'max_concurrent_chats',
        'current_chat_count',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_online' => 'boolean',
        'is_available' => 'boolean',
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
            && $this->is_available
            && $this->current_chat_count < $this->max_concurrent_chats;
    }
}
