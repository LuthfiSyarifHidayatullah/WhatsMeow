<?php

namespace App\Services;

use App\Models\BotResponse;
use App\Models\ChatSession;
use App\Models\Message;
use App\Models\Service;
use App\Models\User;
use App\Events\NewMessageEvent;
use App\Events\ChatEscalatedEvent;
use Illuminate\Support\Str;

class ChatbotService
{
    /**
     * Process incoming message from WhatsApp bot
     */
    public function processIncomingMessage(string $sender, string $chatJID, string $text): array
    {
        // Find or create session
        $session = $this->getOrCreateSession($sender, $chatJID);

        // Store incoming message
        $this->storeMessage($session, 'visitor', $text);

        // Handle based on session status
        return match ($session->status) {
            'bot' => $this->handleBotMode($session, $text),
            'waiting' => $this->handleWaitingMode($session, $text),
            'active' => $this->handleActiveChatMode($session, $text),
            default => $this->getDefaultResponse(),
        };
    }

    /**
     * Get or create a chat session for visitor
     */
    private function getOrCreateSession(string $sender, string $chatJID): ChatSession
    {
        // Check for existing active session
        $session = ChatSession::where('visitor_phone', $sender)
            ->whereNotIn('status', ['resolved', 'abandoned'])
            ->latest()
            ->first();

        if (!$session) {
            $session = ChatSession::create([
                'session_id' => Str::uuid()->toString(),
                'visitor_phone' => $sender,
                'chat_jid' => $chatJID,
                'status' => 'bot',
            ]);
        }

        return $session;
    }

    /**
     * Handle message in bot mode
     */
    private function handleBotMode(ChatSession $session, string $text): array
    {
        $lowerText = strtolower(trim($text));

        // Check for menu commands
        if (in_array($lowerText, ['menu', 'halo', 'hai', 'hi', 'hello', 'start'])) {
            return $this->getMainMenu();
        }

        // Check for escalation request
        if (in_array($lowerText, ['petugas', 'operator', 'live chat', 'bantuan langsung'])) {
            return $this->escalateToOfficer($session, null);
        }

        // Check service selection by number
        if (is_numeric($lowerText)) {
            return $this->handleServiceSelection($session, (int) $lowerText);
        }

        // Try to match with bot responses
        $botResponse = $this->findBotResponse($text);
        if ($botResponse) {
            // Store bot reply
            $this->storeMessage($session, 'bot', $botResponse->response_text);
            return [
                'reply' => $botResponse->response_text,
                'action' => 'bot_reply',
                'session_id' => $session->session_id,
            ];
        }

        // Try keyword matching for services
        $matchedService = $this->matchServiceByKeywords($text);
        if ($matchedService) {
            $session->update(['service_id' => $matchedService->id, 'topic' => $text]);
            $reply = "Pertanyaan Anda terkait *{$matchedService->name}*.\n\n";
            $reply .= "Apakah Anda ingin:\n";
            $reply .= "1. Lihat informasi layanan\n";
            $reply .= "2. Hubungi petugas langsung\n\n";
            $reply .= "Ketik angka pilihan Anda.";

            $this->storeMessage($session, 'bot', $reply);
            return [
                'reply' => $reply,
                'action' => 'bot_reply',
                'session_id' => $session->session_id,
                'service_id' => $matchedService->id,
            ];
        }

        // Default response
        $reply = "Maaf, saya belum memahami pertanyaan Anda. 🙏\n\n";
        $reply .= "Silakan ketik *menu* untuk melihat daftar layanan, atau ketik *petugas* untuk terhubung langsung dengan petugas kami.";

        $this->storeMessage($session, 'bot', $reply);
        return [
            'reply' => $reply,
            'action' => 'bot_reply',
            'session_id' => $session->session_id,
        ];
    }

    /**
     * Handle when visitor selects a service number
     */
    private function handleServiceSelection(ChatSession $session, int $number): array
    {
        // If number is 2 and service already set, escalate
        if ($number === 2 && $session->service_id) {
            return $this->escalateToOfficer($session, $session->service_id);
        }

        $services = Service::where('is_active', true)->orderBy('sort_order')->get();

        if ($number > 0 && $number <= $services->count()) {
            $service = $services[$number - 1];
            $session->update(['service_id' => $service->id]);

            $reply = "📋 *{$service->name}*\n\n";
            $reply .= $service->description ?? "Layanan {$service->name} Pemerintah Kab. Bengkayang.";
            $reply .= "\n\nApakah Anda ingin:\n";
            $reply .= "1. Lihat informasi lebih lanjut\n";
            $reply .= "2. Hubungi petugas langsung\n\n";
            $reply .= "Ketik *menu* untuk kembali ke menu utama.";

            $this->storeMessage($session, 'bot', $reply);
            return [
                'reply' => $reply,
                'action' => 'bot_reply',
                'session_id' => $session->session_id,
                'service_id' => $service->id,
            ];
        }

        return $this->getMainMenu();
    }

    /**
     * Escalate to a human officer
     */
    private function escalateToOfficer(ChatSession $session, ?int $serviceId): array
    {
        if ($serviceId) {
            $session->update(['service_id' => $serviceId]);
        }

        // Find available officer
        $officer = $this->findAvailableOfficer($session->service_id);

        if ($officer) {
            $session->update([
                'status' => 'active',
                'officer_id' => $officer->id,
                'escalated_at' => now(),
                'assigned_at' => now(),
            ]);
            $officer->increment('current_chat_count');

            $serviceName = $officer->service->name ?? 'Layanan Umum';
            $reply = "✅ Anda telah terhubung dengan petugas kami.\n\n";
            $reply .= "👤 *{$officer->name}*\n";
            $reply .= "📌 {$serviceName}\n\n";
            $reply .= "Silakan sampaikan pertanyaan atau keluhan Anda. Ketik *selesai* jika sudah selesai.";

            $this->storeMessage($session, 'bot', $reply);

            // Broadcast event to dashboard
            event(new ChatEscalatedEvent($session));

            return [
                'reply' => $reply,
                'action' => 'escalate',
                'session_id' => $session->session_id,
                'service_id' => $session->service_id,
                'officer_id' => $officer->id,
            ];
        }

        // No officer available, put in queue
        $session->update([
            'status' => 'waiting',
            'escalated_at' => now(),
        ]);

        $reply = "⏳ Mohon maaf, saat ini semua petugas sedang melayani.\n";
        $reply .= "Anda berada dalam antrian. Petugas akan segera merespons.\n\n";
        $reply .= "Sambil menunggu, silakan tuliskan pertanyaan/keluhan Anda.";

        $this->storeMessage($session, 'bot', $reply);

        return [
            'reply' => $reply,
            'action' => 'waiting',
            'session_id' => $session->session_id,
            'service_id' => $session->service_id,
        ];
    }

    /**
     * Handle message when chat is in waiting mode
     */
    private function handleWaitingMode(ChatSession $session, string $text): array
    {
        // Just store the message, notify dashboard
        event(new NewMessageEvent($session, $text, 'visitor'));

        return [
            'reply' => '',  // Don't send auto reply while waiting
            'action' => 'waiting',
            'session_id' => $session->session_id,
        ];
    }

    /**
     * Handle message when chat is active with officer
     */
    private function handleActiveChatMode(ChatSession $session, string $text): array
    {
        $lowerText = strtolower(trim($text));

        // Check if visitor wants to end chat
        if (in_array($lowerText, ['selesai', 'terima kasih', 'done'])) {
            return $this->resolveSession($session);
        }

        // Forward message to officer dashboard via WebSocket
        event(new NewMessageEvent($session, $text, 'visitor'));

        return [
            'reply' => '',  // Officer will reply through dashboard
            'action' => 'forward_to_officer',
            'session_id' => $session->session_id,
            'officer_id' => $session->officer_id,
        ];
    }

    /**
     * Resolve/end a chat session
     */
    private function resolveSession(ChatSession $session): array
    {
        $session->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        if ($session->officer_id) {
            $officer = User::find($session->officer_id);
            if ($officer) {
                $officer->decrement('current_chat_count');
            }
        }

        $reply = "✅ Terima kasih telah menghubungi MPP Kab. Bengkayang! 🙏\n\n";
        $reply .= "Mohon berikan rating layanan kami (1-5):\n";
        $reply .= "1 ⭐ - Sangat Buruk\n";
        $reply .= "2 ⭐⭐ - Buruk\n";
        $reply .= "3 ⭐⭐⭐ - Cukup\n";
        $reply .= "4 ⭐⭐⭐⭐ - Baik\n";
        $reply .= "5 ⭐⭐⭐⭐⭐ - Sangat Baik\n\n";
        $reply .= "Ketik *menu* untuk memulai percakapan baru.";

        $this->storeMessage($session, 'bot', $reply);

        return [
            'reply' => $reply,
            'action' => 'resolved',
            'session_id' => $session->session_id,
        ];
    }

    /**
     * Get main menu response
     */
    private function getMainMenu(): array
    {
        $services = Service::where('is_active', true)->orderBy('sort_order')->get();

        $reply = "🏛️ *Mall Pelayanan Publik*\n";
        $reply .= "*Pemerintah Kabupaten Bengkayang*\n\n";
        $reply .= "Selamat datang! Silakan pilih layanan:\n\n";

        foreach ($services as $index => $service) {
            $reply .= ($index + 1) . ". 📌 {$service->name}\n";
        }

        $reply .= "\n---\n";
        $reply .= "💬 Ketik *petugas* untuk bicara langsung dengan petugas\n";
        $reply .= "ℹ️ Ketik nomor layanan untuk info lebih lanjut";

        return [
            'reply' => $reply,
            'action' => 'bot_reply',
            'session_id' => null,
        ];
    }

    /**
     * Find bot response matching the text
     */
    private function findBotResponse(string $text): ?BotResponse
    {
        $lowerText = strtolower($text);

        // Exact match first
        $response = BotResponse::where('is_active', true)
            ->where('match_type', 'exact')
            ->whereRaw('LOWER(trigger_keyword) = ?', [$lowerText])
            ->orderByDesc('priority')
            ->first();

        if ($response) return $response;

        // Contains match
        $responses = BotResponse::where('is_active', true)
            ->where('match_type', 'contains')
            ->orderByDesc('priority')
            ->get();

        foreach ($responses as $resp) {
            if (str_contains($lowerText, strtolower($resp->trigger_keyword))) {
                return $resp;
            }
        }

        return null;
    }

    /**
     * Match service by keywords
     */
    private function matchServiceByKeywords(string $text): ?Service
    {
        $lowerText = strtolower($text);
        $services = Service::where('is_active', true)->get();

        foreach ($services as $service) {
            $keywords = $service->keywords ?? [];
            foreach ($keywords as $keyword) {
                if (str_contains($lowerText, strtolower($keyword))) {
                    return $service;
                }
            }
        }

        return null;
    }

    /**
     * Find available officer for a service
     */
    private function findAvailableOfficer(?int $serviceId): ?User
    {
        $query = User::where('role', 'officer')
            ->where('is_online', true)
            ->where('is_available', true)
            ->whereColumn('current_chat_count', '<', 'max_concurrent_chats');

        if ($serviceId) {
            // Try service-specific officer first
            $officer = (clone $query)->where('service_id', $serviceId)
                ->orderBy('current_chat_count')
                ->first();

            if ($officer) return $officer;
        }

        // Fallback to any available officer
        return $query->orderBy('current_chat_count')->first();
    }

    /**
     * Store message in database
     */
    private function storeMessage(ChatSession $session, string $senderType, string $content, ?int $userId = null): Message
    {
        return Message::create([
            'chat_session_id' => $session->id,
            'sender_type' => $senderType,
            'sender_user_id' => $userId,
            'content' => $content,
        ]);
    }

    /**
     * Default response
     */
    private function getDefaultResponse(): array
    {
        return [
            'reply' => "Maaf, terjadi kesalahan. Silakan ketik *menu* untuk memulai.",
            'action' => 'bot_reply',
            'session_id' => null,
        ];
    }
}
