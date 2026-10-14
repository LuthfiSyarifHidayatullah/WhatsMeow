<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfoFeedback extends Model
{
    // Laravel memperlakukan "feedback" sebagai kata tak terhitung sehingga
    // menebak tabel 'info_feedback' (tanpa s). Tabel sebenarnya 'info_feedbacks'.
    protected $table = 'info_feedbacks';

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
