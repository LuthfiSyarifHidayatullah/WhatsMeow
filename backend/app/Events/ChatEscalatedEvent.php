<?php

namespace App\Events;

use App\Models\ChatSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatEscalatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatSession $session,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('monitoring'),
        ];

        if ($this->session->officer_id) {
            $channels[] = new Channel('officer.' . $this->session->officer_id);
        }

        if ($this->session->service_id) {
            $channels[] = new Channel('service.' . $this->session->service_id);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->session_id,
            'visitor_phone' => $this->session->visitor_phone,
            'visitor_name' => $this->session->visitor_name,
            'service_id' => $this->session->service_id,
            'officer_id' => $this->session->officer_id,
            'status' => $this->session->status,
            'topic' => $this->session->topic,
            'escalated_at' => $this->session->escalated_at?->toISOString(),
        ];
    }
}
