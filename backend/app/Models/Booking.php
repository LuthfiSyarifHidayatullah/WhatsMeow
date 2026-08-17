<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'service_id', 'title', 'booked_by', 'pic_name', 'pic_phone',
        'date', 'start_time', 'end_time', 'location', 'status', 'notes', 'created_by',
    ];

    protected $casts = ['date' => 'date'];

    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeConfirmed($query) { return $query->where('status', 'confirmed'); }
    public function scopeUpcoming($query) {
        return $query->where('date', '>=', now()->startOfDay())
                     ->where('date', '<=', now()->addDays(30)->endOfDay())
                     ->orderBy('date')->orderBy('start_time');
    }
}
