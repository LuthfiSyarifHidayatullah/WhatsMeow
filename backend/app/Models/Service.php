<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'opd_id',
        'name',
        'code',
        'description',
        'keywords',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function officers(): HasMany
    {
        return $this->hasMany(User::class, 'service_id');
    }

    public function chatSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class);
    }

    public function botResponses(): HasMany
    {
        return $this->hasMany(BotResponse::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(ServiceMenuItem::class)
            ->orderBy('position');
    }

    public function availableOfficers()
    {
        return $this->officers()
            ->where('is_online', true)
            ->where('is_available', true)
            ->whereColumn('current_chat_count', '<', 'max_concurrent_chats');
    }
}
