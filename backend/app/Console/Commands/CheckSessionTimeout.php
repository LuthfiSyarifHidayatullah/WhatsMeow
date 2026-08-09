<?php

namespace App\Console\Commands;

use App\Models\ChatSession;
use App\Models\Message;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\WhatsAppBotService;
use App\Events\NewMessageEvent;
use Illuminate\Console\Command;

class CheckSessionTimeout extends Command
{
    protected $signature = 'chat:check-timeout';
    protected $description = 'Check and auto-disconnect sessions where officer has not responded within 5 minutes';

    public function handle(): void
    {
        $this->checkActiveSessionsTimeout();
        $this->checkWaitingSessionsTimeout();
    }

    /**
     * Check active sessions where officer hasn't responded in 5 minutes
     */
    private function checkActiveSessionsTimeout(): void
    {
        $activeSessions = ChatSession::where('status', 'active')
            ->with(['service', 'officer'])
            ->get();

        foreach ($activeSessions as $session) {
            // Get last visitor message
            $lastVisitorMessage = Message::where('chat_session_id', $session->id)
                ->where('sender_type', 'visitor')
                ->latest()
                ->first();

            if (!$lastVisitorMessage) {
                continue;
            }

            // Get last officer message
            $lastOfficerMessage = Message::where('chat_session_id', $session->id)
                ->where('sender_type', 'officer')
                ->latest()
                ->first();

            // Check if officer hasn't responded after visitor's last message
            $officerInactive = !$lastOfficerMessage ||
                ($lastVisitorMessage->created_at > $lastOfficerMessage->created_at);

            if (!$officerInactive) {
                continue;
            }

            // Check if more than 5 minutes since visitor's last message
            $minutesSinceVisitorMessage = now()->diffInMinutes($lastVisitorMessage->created_at);

            if ($minutesSinceVisitorMessage >= 5) {
                $this->timeoutSession($session, 'active');
                $this->info("Timeout: Session {$session->session_id} (officer inactive)");
            }
        }
    }

    /**
     * Check waiting sessions where no officer picked up in 5 minutes
     */
    private function checkWaitingSessionsTimeout(): void
    {
        $waitingSessions = ChatSession::where('status', 'waiting')
            ->where('escalated_at', '<=', now()->subMinutes(5))
            ->with(['service'])
            ->get();

        foreach ($waitingSessions as $session) {
            $this->timeoutSession($session, 'waiting');
            $this->info("Timeout: Session {$session->session_id} (no officer available)");
        }
    }

    /**
     * Timeout a session - disconnect and notify visitor
     */
    private function timeoutSession(ChatSession $session, string $reason): void
    {
        $officerName = '';

        // Release officer capacity
        if ($session->officer_id) {
            $officer = User::find($session->officer_id);
            if ($officer) {
                $officer->decrement('current_chat_count');
                $officerName = $officer->name;
            }
        }

        // Resolve the session
        $session->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        // Prepare message based on reason
        if ($reason === 'active') {
            $reply = "Mohon maaf, petugas sedang tidak tersedia saat ini.\n\n";
            $reply .= "Silakan ketik *menu* untuk menghubungi kembali.\n";
            $reply .= "Terima kasih atas kesabaran Anda. \xF0\x9F\x99\x8F";
        } else {
            $reply = "Mohon maaf, saat ini tidak ada petugas yang tersedia.\n\n";
            $reply .= "Silakan ketik *menu* untuk menghubungi kembali.\n";
            $reply .= "Terima kasih atas kesabaran Anda. \xF0\x9F\x99\x8F";
        }

        // Store message in database
        Message::create([
            'chat_session_id' => $session->id,
            'sender_type' => 'bot',
            'content' => $reply,
        ]);

        // Send via WhatsApp
        $botService = new WhatsAppBotService();
        $botService->sendMessage($session->chat_jid, $reply);

        // Log activity
        $serviceName = $session->service?->name ?? 'Umum';
        ActivityLog::create([
            'user_id' => $session->officer_id,
            'chat_session_id' => $session->id,
            'action' => 'officer_timeout',
            'description' => "Petugas {$officerName} tidak merespon dalam 5 menit. Layanan: {$serviceName}. Visitor: {$session->visitor_phone}",
            'ip_address' => '0.0.0.0',
        ]);

        // Broadcast to monitoring channel for admin/supervisor notification
        event(new NewMessageEvent(
            $session,
            "TIMEOUT: Petugas {$officerName} tidak merespon chat dari {$session->visitor_phone} ({$serviceName}) dalam 5 menit.",
            'system'
        ));
    }
}
