<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceMenuItem extends Model
{
    protected $fillable = [
        'service_id',
        'position',
        'label',
        'action',
        'response_text',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Daftar action yang didukung oleh chatbot. */
    public const ACTIONS = [
        'info',
        'schedule',
        'formulir_then_escalate',
        'escalate',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
