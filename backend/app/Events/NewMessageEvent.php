<?php

namespace App\Events;

use App\Models\ChatSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatSession $session,
        public string $message,
        public string $senderType,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('chat-session.' . $this->session->session_id),
        ];

        // Also broadcast to monitoring channel
        $channels[] = new Channel('monitoring');

        // Broadcast to service channel if applicable
        if ($this->session->service_id) {
            $channels[] = new PrivateChannel('service.' . $this->session->service_id);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->session_id,
            'message' => $this->message,
            'sender_type' => $this->senderType,
            'visitor_phone' => $this->session->visitor_phone,
            'service_id' => $this->session->service_id,
            'officer_id' => $this->session->officer_id,
            'timestamp' => now()->toISOString(),
        ];
    }
}
