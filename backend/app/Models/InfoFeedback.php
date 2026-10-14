<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfoFeedback extends Model
{
    protected $fillable = [
        'service_id',
        'chat_session_id',
        'info_label',
        'is_helpful',
        'visitor_phone',
    ];

    protected $casts = [
        'is_helpful' => 'boolean',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
