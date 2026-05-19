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
        // FIX #3: Check if rating window expired (>5 min since resolved without rating)
        $this->expireRatingWindow($sender);

        // Check if user is giving a rating for a recently resolved session
        $ratingResult = $this->handleRatingIfApplicable($sender, $text);
        if ($ratingResult) {
            return $ratingResult;
        }

        // Find or create session
        $session = $this->getOrCreateSession($sender, $chatJID);

        // FIX #1: Check session timeout (>7 min idle in active/waiting = auto resolve)
        if ($this->checkAndHandleTimeout($session)) {
            // Session was timed out, create new session
            $session = $this->getOrCreateSession($sender, $chatJID);
        }

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
     * FIX #3: Expire rating window - if >5 min since resolved and no rating, just close it
     */
    private function expireRatingWindow(string $sender): void
    {
        ChatSession::where('visitor_phone', $sender)
            ->where('status', 'resolved')
            ->whereNull('satisfaction_rating')
            ->where('resolved_at', '<', now()->subMinutes(5))
            ->update(['satisfaction_rating' => 0]); // 0 = not rated (expired)
    }

    /**
     * FIX #1: Check if session has timed out (7 min idle)
     */
    private function checkAndHandleTimeout(ChatSession $session): bool
    {
        if (!in_array($session->status, ['active', 'waiting'])) {
            return false;
        }

        // Get last message time
        $lastMessage = Message::where('chat_session_id', $session->id)
            ->latest()
            ->first();

        if (!$lastMessage) {
            return false;
        }

        $minutesSinceLastMessage = now()->diffInMinutes($lastMessage->created_at);

        // Auto-resolve after 7 minutes of inactivity
        if ($minutesSinceLastMessage >= 7) {
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

            return true;
        }

        return false;
    }

    /**
     * Handle rating input if user just resolved a session
     */
    private function handleRatingIfApplicable(string $sender, string $text): ?array
    {
        $lowerText = strtolower(trim($text));

        // Check if input is a rating (1-5)
        if (!in_array($lowerText, ['1', '2', '3', '4', '5'])) {
            return null;
        }

        // Find recently resolved session (within 5 minutes) without a rating
        $session = ChatSession::where('visitor_phone', $sender)
            ->where('status', 'resolved')
            ->whereNull('satisfaction_rating')
            ->where('resolved_at', '>=', now()->subMinutes(5))
            ->latest()
            ->first();

        if (!$session) {
            return null;
        }

        $rating = (int) $lowerText;
        $session->update(['satisfaction_rating' => $rating]);

        $stars = str_repeat('⭐', $rating);
        $reply = "Terima kasih atas rating Anda: {$stars}\n\n";
        $reply .= "Feedback Anda sangat berarti untuk peningkatan layanan kami.\n";
        $reply .= "Ketik *menu* untuk memulai percakapan baru.";

        $this->storeMessage($session, 'bot', $reply);

        return [
            'reply' => $reply,
            'action' => 'rating',
            'session_id' => $session->session_id,
        ];
    }

    /**
     * Get or create a chat session for visitor
     */
    private function getOrCreateSession(string $sender, string $chatJID): ChatSession
    {
        // Check for existing active session (only bot, waiting, or active)
        $session = ChatSession::where('visitor_phone', $sender)
            ->whereIn('status', ['bot', 'waiting', 'active'])
            ->latest()
            ->first();

        if (!$session) {
            $session = ChatSession::create([
                'session_id' => Str::uuid()->toString(),
                'visitor_phone' => $sender,
                'chat_jid' => $chatJID,
                'status' => 'bot',
            ]);

            // Mark as new session so we show welcome menu
            $session->_is_new = true;
        }

        return $session;
    }

    /**
     * Handle message in bot mode
     */
    private function handleBotMode(ChatSession $session, string $text): array
    {
        $lowerText = strtolower(trim($text));

        // If this is a brand new session (returning user), show menu
        if (!empty($session->_is_new)) {
            return $this->getMainMenu();
        }

        // Check for menu commands - FIX #2: reset service_id when going to menu
        if (in_array($lowerText, ['menu', 'halo', 'hai', 'hi', 'hello', 'start'])) {
            $session->update(['service_id' => null, 'topic' => null]);
            return $this->getMainMenu();
        }

        // Check for escalation request - FIX #2: from menu = no service (umum)
        if (in_array($lowerText, ['petugas', 'operator', 'live chat', 'bantuan langsung'])) {
            // Reset service so it goes to "Umum" category
            $session->update(['service_id' => null]);
            return $this->escalateToOfficer($session, null);
        }

        // Check for "selesai" in bot mode too
        if (in_array($lowerText, ['selesai', 'done', 'keluar', 'exit'])) {
            return $this->resolveSession($session);
        }

        // Check service selection by number
        if (is_numeric($lowerText)) {
            return $this->handleServiceSelection($session, (int) $lowerText);
        }

        // Try to match with bot responses
        $botResponse = $this->findBotResponse($text);
        if ($botResponse) {
            // FIX #1: Always add navigation options after bot response
            $replyText = $botResponse->response_text;
            $replyText .= "\n\n---\n";
            $replyText .= "Ketik *menu* untuk kembali ke menu utama\n";
            $replyText .= "Ketik *selesai* untuk mengakhiri sesi\n";
            $replyText .= "Ketik *petugas* untuk bicara dengan petugas";

            $this->storeMessage($session, 'bot', $replyText);
            return [
                'reply' => $replyText,
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

        // Not recognized → show menu
        return $this->getMainMenu();
    }

    /**
     * Handle when visitor selects a service number
     */
    private function handleServiceSelection(ChatSession $session, int $number): array
    {
        // If number is 1 and service already set, show detailed info
        if ($number === 1 && $session->service_id) {
            return $this->showServiceInfo($session);
        }

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
     * Show detailed service information from bot_responses
     * FIX #1: Add selesai/menu option
     */
    private function showServiceInfo(ChatSession $session): array
    {
        $service = Service::find($session->service_id);
        if (!$service) {
            return $this->getMainMenu();
        }

        $responses = BotResponse::where('service_id', $service->id)
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->get();

        $reply = "📋 *Informasi {$service->name}*\n\n";
        $reply .= $service->description ?? '';
        $reply .= "\n\n";

        if ($responses->isNotEmpty()) {
            $reply .= "📌 *Informasi Tersedia:*\n";
            foreach ($responses as $resp) {
                $reply .= "• {$resp->trigger_keyword}\n";
            }
            $reply .= "\nKetik salah satu kata kunci di atas untuk info detail.\n";
        } else {
            $reply .= "Untuk informasi lebih lanjut, silakan hubungi petugas.\n";
        }

        $reply .= "\n---\n";
        $reply .= "Ketik *2* untuk hubungi petugas langsung\n";
        $reply .= "Ketik *menu* untuk kembali ke menu utama\n";
        $reply .= "Ketik *selesai* untuk mengakhiri sesi";

        $this->storeMessage($session, 'bot', $reply);
        return [
            'reply' => $reply,
            'action' => 'bot_reply',
            'session_id' => $session->session_id,
            'service_id' => $service->id,
        ];
    }

    /**
     * Escalate to a human officer
     */
    private function escalateToOfficer(ChatSession $session, ?int $serviceId): array
    {
        if ($serviceId) {
            $session->update(['service_id' => $serviceId]);
        }

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
            event(new ChatEscalatedEvent($session));

            return [
                'reply' => $reply,
                'action' => 'escalate',
                'session_id' => $session->session_id,
                'service_id' => $session->service_id,
                'officer_id' => $officer->id,
            ];
        }

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
        event(new NewMessageEvent($session, $text, 'visitor'));

        return [
            'reply' => '',
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

        if (in_array($lowerText, ['selesai', 'terima kasih', 'done'])) {
            return $this->resolveSession($session);
        }

        event(new NewMessageEvent($session, $text, 'visitor'));

        return [
            'reply' => '',
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
        $reply .= "Ketik *menu* untuk memulai percakapan baru.\n";
        $reply .= "_Rating akan otomatis ditutup dalam 5 menit._";

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

    private function findBotResponse(string $text): ?BotResponse
    {
        $lowerText = strtolower($text);

        $response = BotResponse::where('is_active', true)
            ->where('match_type', 'exact')
            ->whereRaw('LOWER(trigger_keyword) = ?', [$lowerText])
            ->orderByDesc('priority')
            ->first();

        if ($response) return $response;

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

    private function findAvailableOfficer(?int $serviceId): ?User
    {
        $query = User::where('role', 'officer')
            ->where('is_online', true)
            ->where('is_available', true)
            ->whereColumn('current_chat_count', '<', 'max_concurrent_chats');

        if ($serviceId) {
            $officer = (clone $query)->where('service_id', $serviceId)
                ->orderBy('current_chat_count')
                ->first();

            if ($officer) return $officer;
        }

        return $query->orderBy('current_chat_count')->first();
    }

    private function storeMessage(ChatSession $session, string $senderType, string $content, ?int $userId = null): Message
    {
        return Message::create([
            'chat_session_id' => $session->id,
            'sender_type' => $senderType,
            'sender_user_id' => $userId,
            'content' => $content,
        ]);
    }

    private function getDefaultResponse(): array
    {
        return [
            'reply' => "Maaf, terjadi kesalahan. Silakan ketik *menu* untuk memulai.",
            'action' => 'bot_reply',
            'session_id' => null,
        ];
    }
}
