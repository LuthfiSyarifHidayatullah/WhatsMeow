<?php

namespace App\Services;

use App\Models\Booking;
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
     * Sub-menu definitions per service code
     */
    private array $serviceMenus = [
        'domain' => [
            'title' => 'Domain Bengkayang.go.id',
            'items' => [
                1 => ['label' => 'Formulir Pengajuan', 'action' => 'info', 'key' => 'formulir'],
                2 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'zoom' => [
            'title' => 'Zoom Meeting/Video Conference',
            'items' => [
                1 => ['label' => 'Informasi Jadwal', 'action' => 'schedule'],
                2 => ['label' => 'Formulir Pengajuan', 'action' => 'info', 'key' => 'formulir'],
                3 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'dokumentasi' => [
            'title' => 'Fasilitasi Dokumentasi Kegiatan',
            'items' => [
                1 => ['label' => 'Informasi Jadwal', 'action' => 'schedule'],
                2 => ['label' => 'Formulir Pengajuan', 'action' => 'info', 'key' => 'formulir'],
                3 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'tte' => [
            'title' => 'Tanda Tangan Elektronik (TTE)',
            'items' => [
                1 => ['label' => 'Informasi Jadwal', 'action' => 'schedule'],
                2 => ['label' => 'Formulir Pengajuan', 'action' => 'info', 'key' => 'formulir'],
                3 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'alat' => [
            'title' => 'Alat dan Operator Kegiatan',
            'items' => [
                1 => ['label' => 'Informasi Jadwal', 'action' => 'schedule'],
                2 => ['label' => 'Formulir Pengajuan', 'action' => 'info', 'key' => 'formulir'],
                3 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
    ];

    /**
     * Process incoming message from WhatsApp bot
     */
    public function processIncomingMessage(string $sender, string $chatJID, string $text): array
    {
        $this->expireRatingWindow($sender);

        $ratingResult = $this->handleRatingIfApplicable($sender, $text);
        if ($ratingResult) {
            return $ratingResult;
        }

        $session = $this->getOrCreateSession($sender, $chatJID);

        if ($this->checkAndHandleTimeout($session)) {
            $session = $this->getOrCreateSession($sender, $chatJID);
        }

        $this->storeMessage($session, 'visitor', $text);

        return match ($session->status) {
            'bot' => $this->handleBotMode($session, $text),
            'waiting' => $this->handleWaitingMode($session, $text),
            'active' => $this->handleActiveChatMode($session, $text),
            default => $this->getDefaultResponse(),
        };
    }

    /**
     * Handle message in bot mode
     */
    private function handleBotMode(ChatSession $session, string $text): array
    {
        $lowerText = strtolower(trim($text));

        // New session → show main menu
        if (!empty($session->_is_new)) {
            return $this->getMainMenu($session);
        }

        // Menu commands
        if (in_array($lowerText, ['menu', '0', 'halo', 'hai', 'hi', 'hello', 'start'])) {
            $session->update(['service_id' => null, 'topic' => null]);
            return $this->getMainMenu($session);
        }

        // Back command (9) → go back to service sub-menu if service selected
        if ($lowerText === '9') {
            if ($session->service_id) {
                return $this->getServiceSubMenu($session);
            }
            return $this->getMainMenu($session);
        }

        // Exit
        if (in_array($lowerText, ['selesai', 'done', 'keluar', 'exit'])) {
            return $this->resolveSession($session);
        }

        // Direct escalation
        if (in_array($lowerText, ['petugas', 'operator', 'live chat'])) {
            return $this->escalateToOfficer($session, $session->service_id);
        }

        // Numeric input
        if (is_numeric($lowerText)) {
            $number = (int) $lowerText;

            // If service already selected → handle sub-menu selection
            if ($session->service_id) {
                return $this->handleSubMenuSelection($session, $number);
            }

            // Otherwise → handle main menu service selection
            return $this->handleMainMenuSelection($session, $number);
        }

        // Keyword matching
        $matchedService = $this->matchServiceByKeywords($text);
        if ($matchedService) {
            $session->update(['service_id' => $matchedService->id, 'topic' => $text]);
            return $this->getServiceSubMenu($session);
        }

        // Not recognized → show main menu
        return $this->getMainMenu($session);
    }

    /**
     * Handle main menu number selection (1-5 = select service)
     */
    private function handleMainMenuSelection(ChatSession $session, int $number): array
    {
        $services = Service::where('is_active', true)->orderBy('sort_order')->get();

        if ($number > 0 && $number <= $services->count()) {
            $service = $services[$number - 1];
            $session->update(['service_id' => $service->id, 'topic' => $service->name]);
            return $this->getServiceSubMenu($session);
        }

        return $this->getMainMenu($session);
    }

    /**
     * Handle sub-menu number selection within a service
     */
    private function handleSubMenuSelection(ChatSession $session, int $number): array
    {
        $service = Service::find($session->service_id);
        if (!$service) {
            return $this->getMainMenu($session);
        }

        $menuDef = $this->serviceMenus[$service->code] ?? null;
        if (!$menuDef || !isset($menuDef['items'][$number])) {
            return $this->getServiceSubMenu($session);
        }

        $item = $menuDef['items'][$number];

        // Escalate to officer
        if ($item['action'] === 'escalate') {
            return $this->escalateToOfficer($session, $session->service_id);
        }

        // Show real-time schedule from bookings table
        if ($item['action'] === 'schedule') {
            return $this->showSchedule($session, $service);
        }

        // Show info from bot_responses
        return $this->showSubMenuInfo($session, $service, $item);
    }

    /**
     * Show real-time schedule from bookings table (30 days ahead)
     */
    private function showSchedule(ChatSession $session, Service $service): array
    {
        $bookings = Booking::where('service_id', $service->id)
            ->where('status', 'confirmed')
            ->where('date', '>=', now()->startOfDay())
            ->where('date', '<=', now()->addDays(30)->endOfDay())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        if ($bookings->isEmpty()) {
            $reply = "📅 *Jadwal {$service->name}*\n\n";
            $reply .= "Tidak ada jadwal kegiatan dalam 30 hari ke depan.\n";
            $reply .= "Semua ruangan/alat tersedia untuk digunakan.";
        } else {
            $reply = "📅 *Jadwal {$service->name}*\n";
            $reply .= "_(30 hari ke depan)_\n\n";

            $grouped = $bookings->groupBy(fn($b) => $b->date->format('Y-m-d'));

            $count = 0;
            foreach ($grouped as $date => $dayBookings) {
                if ($count >= 10) {
                    $remaining = $grouped->count() - 10;
                    $reply .= "\n_...dan {$remaining} hari lainnya._\n";
                    $reply .= "_Hubungi petugas untuk jadwal lengkap._";
                    break;
                }

                $carbonDate = \Carbon\Carbon::parse($date);
                $dayLabel = $carbonDate->isToday() ? 'Hari Ini' : ($carbonDate->isTomorrow() ? 'Besok' : $carbonDate->translatedFormat('l'));
                $reply .= "📆 *{$dayLabel}, {$carbonDate->format('d/m/Y')}*\n";

                foreach ($dayBookings as $booking) {
                    $time = substr($booking->start_time, 0, 5) . ' - ' . substr($booking->end_time, 0, 5);
                    $reply .= "• {$time} WIB | {$booking->title}\n";
                    $reply .= "  📍 {$booking->location} | {$booking->booked_by}\n";
                }
                $reply .= "\n";
                $count++;
            }
        }

        $reply .= "\n---\n";
        $reply .= "Ketik *9* untuk kembali\n";
        $reply .= "Ketik *0* untuk menu utama";

        $this->storeMessage($session, 'bot', $reply);
        return [
            'reply' => $reply,
            'action' => 'bot_reply',
            'session_id' => $session->session_id,
            'service_id' => $service->id,
        ];
    }

    /**
     * Show information for a sub-menu item (from bot_responses table)
     */
    private function showSubMenuInfo(ChatSession $session, Service $service, array $item): array
    {
        $key = $item['key'];

        // Find bot response matching this service + key
        $botResponse = BotResponse::where('service_id', $service->id)
            ->where('trigger_keyword', $key)
            ->where('is_active', true)
            ->first();

        if ($botResponse) {
            $reply = $botResponse->response_text;
        } else {
            $reply = "ℹ️ *{$item['label']}*\n\n";
            $reply .= "Informasi untuk {$item['label']} layanan {$service->name} belum tersedia.\n";
            $reply .= "Silakan hubungi petugas untuk informasi lebih lanjut.";
        }

        $reply .= "\n\n---\n";
        $reply .= "Ketik *9* untuk kembali\n";
        $reply .= "Ketik *0* untuk menu utama";

        $this->storeMessage($session, 'bot', $reply);
        return [
            'reply' => $reply,
            'action' => 'bot_reply',
            'session_id' => $session->session_id,
            'service_id' => $service->id,
        ];
    }

    /**
     * Get main menu
     */
    private function getMainMenu(?ChatSession $session = null): array
    {
        $services = Service::where('is_active', true)->orderBy('sort_order')->get();

        $reply = "📋 *SISTEM INFORMASI PELAYANAN*\n";
        $reply .= "*DINAS KOMUNIKASI DAN INFORMATIKA*\n";
        $reply .= "*KABUPATEN BENGKAYANG*\n\n";
        $reply .= "Silakan pilih pelayanan:\n\n";

        foreach ($services as $index => $service) {
            $reply .= ($index + 1) . ". {$service->name}\n";
        }

        $reply .= "\nKetik angka sesuai pelayanan yang dibutuhkan.";

        if ($session) {
            $this->storeMessage($session, 'bot', $reply);
        }

        return [
            'reply' => $reply,
            'action' => 'bot_reply',
            'session_id' => $session?->session_id,
        ];
    }

    /**
     * Get service sub-menu
     */
    private function getServiceSubMenu(ChatSession $session): array
    {
        $service = Service::find($session->service_id);
        if (!$service) {
            return $this->getMainMenu($session);
        }

        $menuDef = $this->serviceMenus[$service->code] ?? null;

        $reply = "📋 *{$service->name}*\n\n";
        $reply .= "Pilih informasi yang dibutuhkan:\n\n";

        if ($menuDef) {
            foreach ($menuDef['items'] as $num => $item) {
                $reply .= "{$num}. {$item['label']}\n";
            }
        } else {
            $reply .= "1. Informasi Umum\n";
            $reply .= "6. Hubungi Petugas\n";
        }

        $reply .= "\n9. Kembali\n";
        $reply .= "0. Menu Utama";

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
            $reply .= "Silakan sampaikan pertanyaan Anda.\n";
            $reply .= "Ketik *selesai* jika sudah selesai.";

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

        $reply = "⏳ Mohon maaf, saat ini petugas sedang melayani.\n";
        $reply .= "Anda berada dalam antrian. Petugas akan segera merespons.\n\n";
        $reply .= "Sambil menunggu, silakan tuliskan pertanyaan Anda.";

        $this->storeMessage($session, 'bot', $reply);

        return [
            'reply' => $reply,
            'action' => 'waiting',
            'session_id' => $session->session_id,
            'service_id' => $session->service_id,
        ];
    }

    // =====================================================
    // HELPER METHODS (unchanged logic)
    // =====================================================

    private function handleWaitingMode(ChatSession $session, string $text): array
    {
        if (empty($session->topic)) {
            $session->update(['topic' => mb_substr($text, 0, 255)]);
        }
        event(new NewMessageEvent($session, $text, 'visitor'));
        return ['reply' => '', 'action' => 'waiting', 'session_id' => $session->session_id];
    }

    private function handleActiveChatMode(ChatSession $session, string $text): array
    {
        $lowerText = strtolower(trim($text));
        if (in_array($lowerText, ['selesai', 'terima kasih', 'done'])) {
            return $this->resolveSession($session);
        }
        if (empty($session->topic)) {
            $session->update(['topic' => mb_substr($text, 0, 255)]);
        }
        event(new NewMessageEvent($session, $text, 'visitor'));
        return ['reply' => '', 'action' => 'forward_to_officer', 'session_id' => $session->session_id, 'officer_id' => $session->officer_id];
    }

    private function resolveSession(ChatSession $session): array
    {
        $session->update(['status' => 'resolved', 'resolved_at' => now()]);
        if ($session->officer_id) {
            $officer = User::find($session->officer_id);
            if ($officer) $officer->decrement('current_chat_count');
        }

        $reply = "✅ Terima kasih telah menghubungi Diskominfo Kab. Bengkayang! 🙏\n\n";
        $reply .= "Pengajuan Anda sedang diproses. Kami akan memberitahu setelah selesai.\n";
        $reply .= "Ketik *menu* untuk memulai percakapan baru.";

        $this->storeMessage($session, 'bot', $reply);
        return ['reply' => $reply, 'action' => 'resolved', 'session_id' => $session->session_id];
    }

    private function expireRatingWindow(string $sender): void
    {
        ChatSession::where('visitor_phone', $sender)
            ->where('status', 'resolved')
            ->whereNull('satisfaction_rating')
            ->where('resolved_at', '<', now()->subMinutes(30))
            ->update(['satisfaction_rating' => 0]);
    }

    private function handleRatingIfApplicable(string $sender, string $text): ?array
    {
        $lowerText = strtolower(trim($text));
        if (!in_array($lowerText, ['1', '2', '3', '4', '5'])) return null;

        $activeSession = ChatSession::where('visitor_phone', $sender)
            ->whereIn('status', ['bot', 'waiting', 'active'])->first();
        if ($activeSession) return null;

        // Find session awaiting rating (within 30 minutes of notification being sent)
        $session = ChatSession::where('visitor_phone', $sender)
            ->where('status', 'resolved')
            ->whereNull('satisfaction_rating')
            ->where('resolved_at', '>=', now()->subMinutes(30))
            ->latest()->first();
        if (!$session) return null;

        $rating = (int) $lowerText;
        $session->update(['satisfaction_rating' => $rating]);

        $stars = str_repeat('⭐', $rating);
        $reply = "Terima kasih atas rating Anda: {$stars}\n\n";
        $reply .= "Feedback Anda sangat berarti untuk peningkatan layanan kami.\n";
        $reply .= "Ketik *menu* untuk memulai percakapan baru.";

        $this->storeMessage($session, 'bot', $reply);
        return ['reply' => $reply, 'action' => 'rating', 'session_id' => $session->session_id];
    }

    private function getOrCreateSession(string $sender, string $chatJID): ChatSession
    {
        $session = ChatSession::where('visitor_phone', $sender)
            ->whereIn('status', ['bot', 'waiting', 'active'])->latest()->first();

        if (!$session) {
            $session = ChatSession::create([
                'session_id' => Str::uuid()->toString(),
                'visitor_phone' => $sender,
                'chat_jid' => $chatJID,
                'status' => 'bot',
            ]);
            $session->_is_new = true;
        }
        return $session;
    }

    private function checkAndHandleTimeout(ChatSession $session): bool
    {
        if (!in_array($session->status, ['active', 'waiting'])) return false;

        $lastMessage = Message::where('chat_session_id', $session->id)->latest()->first();
        if (!$lastMessage) return false;

        $minutesSinceLastMessage = now()->diffInMinutes($lastMessage->created_at);

        if ($session->status === 'active' && $minutesSinceLastMessage >= 5) {
            $lastOfficerMessage = Message::where('chat_session_id', $session->id)->where('sender_type', 'officer')->latest()->first();
            $lastVisitorMessage = Message::where('chat_session_id', $session->id)->where('sender_type', 'visitor')->latest()->first();
            $officerInactive = !$lastOfficerMessage || ($lastVisitorMessage && $lastVisitorMessage->created_at > $lastOfficerMessage->created_at);
            if ($officerInactive && $lastVisitorMessage && now()->diffInMinutes($lastVisitorMessage->created_at) >= 5) {
                return $this->handleOfficerTimeout($session);
            }
        }

        if ($session->status === 'waiting' && $minutesSinceLastMessage >= 5) {
            return $this->handleWaitingTimeout($session);
        }

        return false;
    }

    private function handleOfficerTimeout(ChatSession $session): bool
    {
        $officerName = '';
        if ($session->officer_id) {
            $officer = User::find($session->officer_id);
            if ($officer) { $officer->decrement('current_chat_count'); $officerName = $officer->name; }
        }
        $session->update(['status' => 'resolved', 'resolved_at' => now()]);

        $reply = "Mohon maaf, petugas sedang tidak tersedia saat ini.\n\n";
        $reply .= "Silakan ketik *menu* untuk menghubungi kembali.\n";
        $reply .= "Terima kasih atas kesabaran Anda. 🙏";

        $this->storeMessage($session, 'bot', $reply);
        (new \App\Services\WhatsAppBotService())->sendMessage($session->chat_jid, $reply);
        $this->notifyAdminOfficerTimeout($session, $officerName);
        return true;
    }

    private function handleWaitingTimeout(ChatSession $session): bool
    {
        $session->update(['status' => 'resolved', 'resolved_at' => now()]);

        $reply = "Mohon maaf, saat ini tidak ada petugas yang tersedia.\n\n";
        $reply .= "Silakan ketik *menu* untuk menghubungi kembali.\n";
        $reply .= "Terima kasih atas kesabaran Anda. 🙏";

        $this->storeMessage($session, 'bot', $reply);
        (new \App\Services\WhatsAppBotService())->sendMessage($session->chat_jid, $reply);
        $this->notifyAdminOfficerTimeout($session, 'Tidak ada petugas');
        return true;
    }

    private function notifyAdminOfficerTimeout(ChatSession $session, string $officerName): void
    {
        $serviceName = $session->service ? $session->service->name : 'Umum';
        \App\Models\ActivityLog::create([
            'user_id' => $session->officer_id,
            'chat_session_id' => $session->id,
            'action' => 'officer_timeout',
            'description' => "Petugas {$officerName} tidak merespon dalam 5 menit. Layanan: {$serviceName}. Visitor: {$session->visitor_phone}",
            'ip_address' => '0.0.0.0',
        ]);
        event(new NewMessageEvent($session, "TIMEOUT: Petugas {$officerName} tidak merespon ({$serviceName})", 'system'));
    }

    private function matchServiceByKeywords(string $text): ?Service
    {
        $lowerText = strtolower($text);
        $services = Service::where('is_active', true)->get();
        foreach ($services as $service) {
            foreach (($service->keywords ?? []) as $keyword) {
                if (str_contains($lowerText, strtolower($keyword))) return $service;
            }
        }
        return null;
    }

    private function findAvailableOfficer(?int $serviceId): ?User
    {
        $query = User::where('role', 'officer')->where('is_online', true)
            ->where('is_available', true)->whereColumn('current_chat_count', '<', 'max_concurrent_chats');

        if ($serviceId) {
            return (clone $query)->where('service_id', $serviceId)->orderBy('current_chat_count')->first();
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
        return ['reply' => "Maaf, terjadi kesalahan. Silakan ketik *menu* untuk memulai.", 'action' => 'bot_reply', 'session_id' => null];
    }
}
