<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    protected $fillable = [
        'session_id',
        'visitor_phone',
        'visitor_name',
        'chat_jid',
        'service_id',
        'officer_id',
        'status',
        'priority',
        'topic',
        'satisfaction_rating',
        'satisfaction_feedback',
        'escalated_at',
        'assigned_at',
        'resolved_at',
    ];

    protected $casts = [
        'escalated_at' => 'datetime',
        'assigned_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeBot($query)
    {
        return $query->where('status', 'bot');
    }

    public function scopeForOfficer($query, $officerId)
    {
        return $query->where('officer_id', $officerId);
    }

    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }
}
