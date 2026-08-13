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
                1 => ['label' => 'Persyaratan', 'action' => 'info', 'key' => 'persyaratan'],
                2 => ['label' => 'Prosedur Pelayanan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Informasi Domain Tersedia', 'action' => 'info', 'key' => 'info_domain'],
                4 => ['label' => 'Formulir Pengajuan Domain', 'action' => 'info', 'key' => 'formulir'],
                5 => ['label' => 'Bantuan/Gangguan', 'action' => 'info', 'key' => 'bantuan'],
                6 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'zoom' => [
            'title' => 'Zoom Meeting/Video Conference',
            'items' => [
                1 => ['label' => 'Persyaratan', 'action' => 'info', 'key' => 'persyaratan'],
                2 => ['label' => 'Prosedur Pelayanan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Informasi Jadwal dan Ketersediaan', 'action' => 'schedule'],
                4 => ['label' => 'Formulir Pengajuan Jadwal', 'action' => 'info', 'key' => 'formulir'],
                5 => ['label' => 'Bantuan/Gangguan', 'action' => 'info', 'key' => 'bantuan'],
                6 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'informasi' => [
            'title' => 'Informasi Publik',
            'items' => [
                1 => ['label' => 'Persyaratan', 'action' => 'info', 'key' => 'persyaratan'],
                2 => ['label' => 'Prosedur Pelayanan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Daftar Informasi Publik', 'action' => 'info', 'key' => 'daftar_info'],
                4 => ['label' => 'Formulir Permohonan Informasi', 'action' => 'info', 'key' => 'formulir'],
                5 => ['label' => 'Bantuan/Gangguan', 'action' => 'info', 'key' => 'bantuan'],
                6 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'tte' => [
            'title' => 'Tanda Tangan Elektronik (TTE)',
            'items' => [
                1 => ['label' => 'Persyaratan', 'action' => 'info', 'key' => 'persyaratan'],
                2 => ['label' => 'Prosedur Pelayanan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Informasi Status Pengajuan', 'action' => 'info', 'key' => 'status'],
                4 => ['label' => 'Formulir Pengajuan TTE', 'action' => 'info', 'key' => 'formulir'],
                5 => ['label' => 'Bantuan/Gangguan', 'action' => 'info', 'key' => 'bantuan'],
                6 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'alat' => [
            'title' => 'Alat dan Operator Kegiatan',
            'items' => [
                1 => ['label' => 'Persyaratan', 'action' => 'info', 'key' => 'persyaratan'],
                2 => ['label' => 'Prosedur Peminjaman', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Jadwal Pemakaian Alat', 'action' => 'schedule'],
                4 => ['label' => 'Formulir Pengajuan Peminjaman', 'action' => 'info', 'key' => 'formulir'],
                5 => ['label' => 'Bantuan/Gangguan', 'action' => 'info', 'key' => 'bantuan'],
                6 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
    ];

    public function processIncomingMessage(string $sender, string $chatJID, string $text): array
    {
        $this->expireRatingWindow($sender);

        $ratingResult = $this->handleRatingIfApplicable($sender, $text);
        if ($ratingResult) return $ratingResult;

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

    private function handleBotMode(ChatSession $session, string $text): array
    {
        $lowerText = strtolower(trim($text));

        if (!empty($session->_is_new)) {
            return $this->getMainMenu($session);
        }

        // Menu / reset commands
        if (in_array($lowerText, ['menu', '0', 'halo', 'hai', 'hi', 'hello', 'start'])) {
            $session->update(['service_id' => null, 'topic' => null]);
            return $this->getMainMenu($session);
        }

        // Back (9) → return to service sub-menu
        if ($lowerText === '9') {
            if ($session->service_id) return $this->getServiceSubMenu($session);
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
            if ($session->service_id) return $this->handleSubMenuSelection($session, $number);
            return $this->handleMainMenuSelection($session, $number);
        }

        // Keyword matching
        $matchedService = $this->matchServiceByKeywords($text);
        if ($matchedService) {
            $session->update(['service_id' => $matchedService->id, 'topic' => $text]);
            return $this->getServiceSubMenu($session);
        }

        return $this->getMainMenu($session);
    }

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

    private function handleSubMenuSelection(ChatSession $session, int $number): array
    {
        $service = Service::find($session->service_id);
        if (!$service) return $this->getMainMenu($session);

        $menuDef = $this->serviceMenus[$service->code] ?? null;
        if (!$menuDef || !isset($menuDef['items'][$number])) {
            return $this->getServiceSubMenu($session);
        }

        $item = $menuDef['items'][$number];

        if ($item['action'] === 'escalate') {
            return $this->escalateToOfficer($session, $session->service_id);
        }

        if ($item['action'] === 'schedule') {
            return $this->showSchedule($session, $service);
        }

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

    private function showSubMenuInfo(ChatSession $session, Service $service, array $item): array
    {
        $key = $item['key'];
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

        if ($session) $this->storeMessage($session, 'bot', $reply);

        return ['reply' => $reply, 'action' => 'bot_reply', 'session_id' => $session?->session_id];
    }

    private function getServiceSubMenu(ChatSession $session): array
    {
        $service = Service::find($session->service_id);
        if (!$service) return $this->getMainMenu($session);

        $menuDef = $this->serviceMenus[$service->code] ?? null;

        $reply = "📋 *{$service->name}*\n\n";
        $reply .= "Pilih informasi yang dibutuhkan:\n\n";

        if ($menuDef) {
            foreach ($menuDef['items'] as $num => $item) {
                $reply .= "{$num}. {$item['label']}\n";
            }
        }

        $reply .= "\n9. Kembali\n";
        $reply .= "0. Menu Utama";

        $this->storeMessage($session, 'bot', $reply);
        return ['reply' => $reply, 'action' => 'bot_reply', 'session_id' => $session->session_id, 'service_id' => $service->id];
    }

    private function escalateToOfficer(ChatSession $session, ?int $serviceId): array
    {
        if ($serviceId) $session->update(['service_id' => $serviceId]);

        $officer = $this->findAvailableOfficer($session->service_id);

        if ($officer) {
            $session->update(['status' => 'active', 'officer_id' => $officer->id, 'escalated_at' => now(), 'assigned_at' => now()]);
            $officer->increment('current_chat_count');

            $serviceName = $officer->service->name ?? 'Layanan Umum';
            $reply = "✅ Anda telah terhubung dengan petugas kami.\n\n";
            $reply .= "👤 *{$officer->name}*\n";
            $reply .= "📌 {$serviceName}\n\n";
            $reply .= "Silakan sampaikan pertanyaan Anda.\nKetik *selesai* jika sudah selesai.";

            $this->storeMessage($session, 'bot', $reply);
            event(new ChatEscalatedEvent($session));
            return ['reply' => $reply, 'action' => 'escalate', 'session_id' => $session->session_id, 'service_id' => $session->service_id, 'officer_id' => $officer->id];
        }

        $session->update(['status' => 'waiting', 'escalated_at' => now()]);
        $reply = "⏳ Mohon maaf, saat ini petugas sedang melayani.\nAnda berada dalam antrian. Petugas akan segera merespons.\n\nSambil menunggu, silakan tuliskan pertanyaan Anda.";
        $this->storeMessage($session, 'bot', $reply);
        return ['reply' => $reply, 'action' => 'waiting', 'session_id' => $session->session_id, 'service_id' => $session->service_id];
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    private function handleWaitingMode(ChatSession $session, string $text): array
    {
        if (empty($session->topic)) $session->update(['topic' => mb_substr($text, 0, 255)]);
        event(new NewMessageEvent($session, $text, 'visitor'));
        return ['reply' => '', 'action' => 'waiting', 'session_id' => $session->session_id];
    }

    private function handleActiveChatMode(ChatSession $session, string $text): array
    {
        $lowerText = strtolower(trim($text));
        if (in_array($lowerText, ['selesai', 'terima kasih', 'done'])) return $this->resolveSession($session);
        if (empty($session->topic)) $session->update(['topic' => mb_substr($text, 0, 255)]);
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
        $reply .= "Mohon berikan rating layanan kami (1-5):\n";
        $reply .= "1 ⭐ - Sangat Buruk\n2 ⭐⭐ - Buruk\n3 ⭐⭐⭐ - Cukup\n4 ⭐⭐⭐⭐ - Baik\n5 ⭐⭐⭐⭐⭐ - Sangat Baik\n\n";
        $reply .= "Ketik *menu* untuk memulai percakapan baru.";
        $this->storeMessage($session, 'bot', $reply);
        return ['reply' => $reply, 'action' => 'resolved', 'session_id' => $session->session_id];
    }

    private function expireRatingWindow(string $sender): void
    {
        ChatSession::where('visitor_phone', $sender)->where('status', 'resolved')
            ->whereNull('satisfaction_rating')->where('resolved_at', '<', now()->subMinutes(5))
            ->update(['satisfaction_rating' => 0]);
    }

    private function handleRatingIfApplicable(string $sender, string $text): ?array
    {
        $lowerText = strtolower(trim($text));
        if (!in_array($lowerText, ['1', '2', '3', '4', '5'])) return null;

        $activeSession = ChatSession::where('visitor_phone', $sender)->whereIn('status', ['bot', 'waiting', 'active'])->first();
        if ($activeSession) return null;

        $session = ChatSession::where('visitor_phone', $sender)->where('status', 'resolved')
            ->whereNull('satisfaction_rating')->where('resolved_at', '>=', now()->subMinutes(5))->latest()->first();
        if (!$session) return null;

        $rating = (int) $lowerText;
        $session->update(['satisfaction_rating' => $rating]);
        $stars = str_repeat('⭐', $rating);
        $reply = "Terima kasih atas rating Anda: {$stars}\n\nFeedback Anda sangat berarti untuk peningkatan layanan kami.\nKetik *menu* untuk memulai percakapan baru.";
        $this->storeMessage($session, 'bot', $reply);
        return ['reply' => $reply, 'action' => 'rating', 'session_id' => $session->session_id];
    }

    private function getOrCreateSession(string $sender, string $chatJID): ChatSession
    {
        $session = ChatSession::where('visitor_phone', $sender)->whereIn('status', ['bot', 'waiting', 'active'])->latest()->first();
        if (!$session) {
            $session = ChatSession::create(['session_id' => Str::uuid()->toString(), 'visitor_phone' => $sender, 'chat_jid' => $chatJID, 'status' => 'bot']);
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
            $lastOfficerMsg = Message::where('chat_session_id', $session->id)->where('sender_type', 'officer')->latest()->first();
            $lastVisitorMsg = Message::where('chat_session_id', $session->id)->where('sender_type', 'visitor')->latest()->first();
            $officerInactive = !$lastOfficerMsg || ($lastVisitorMsg && $lastVisitorMsg->created_at > $lastOfficerMsg->created_at);
            if ($officerInactive && $lastVisitorMsg && now()->diffInMinutes($lastVisitorMsg->created_at) >= 5) {
                return $this->handleOfficerTimeout($session);
            }
        }
        if ($session->status === 'waiting' && $minutesSinceLastMessage >= 5) return $this->handleWaitingTimeout($session);
        return false;
    }

    private function handleOfficerTimeout(ChatSession $session): bool
    {
        $officerName = '';
        if ($session->officer_id) { $officer = User::find($session->officer_id); if ($officer) { $officer->decrement('current_chat_count'); $officerName = $officer->name; } }
        $session->update(['status' => 'resolved', 'resolved_at' => now()]);
        $reply = "Mohon maaf, petugas sedang tidak tersedia saat ini.\n\nSilakan ketik *menu* untuk menghubungi kembali.\nTerima kasih atas kesabaran Anda. 🙏";
        $this->storeMessage($session, 'bot', $reply);
        (new \App\Services\WhatsAppBotService())->sendMessage($session->chat_jid, $reply);
        $this->notifyAdminOfficerTimeout($session, $officerName);
        return true;
    }

    private function handleWaitingTimeout(ChatSession $session): bool
    {
        $session->update(['status' => 'resolved', 'resolved_at' => now()]);
        $reply = "Mohon maaf, saat ini tidak ada petugas yang tersedia.\n\nSilakan ketik *menu* untuk menghubungi kembali.\nTerima kasih atas kesabaran Anda. 🙏";
        $this->storeMessage($session, 'bot', $reply);
        (new \App\Services\WhatsAppBotService())->sendMessage($session->chat_jid, $reply);
        $this->notifyAdminOfficerTimeout($session, 'Tidak ada petugas');
        return true;
    }

    private function notifyAdminOfficerTimeout(ChatSession $session, string $officerName): void
    {
        $serviceName = $session->service ? $session->service->name : 'Umum';
        \App\Models\ActivityLog::create(['user_id' => $session->officer_id, 'chat_session_id' => $session->id, 'action' => 'officer_timeout', 'description' => "Petugas {$officerName} tidak merespon dalam 5 menit. Layanan: {$serviceName}. Visitor: {$session->visitor_phone}", 'ip_address' => '0.0.0.0']);
        event(new NewMessageEvent($session, "TIMEOUT: Petugas {$officerName} tidak merespon ({$serviceName})", 'system'));
    }

    private function matchServiceByKeywords(string $text): ?Service
    {
        $lowerText = strtolower($text);
        foreach (Service::where('is_active', true)->get() as $service) {
            foreach (($service->keywords ?? []) as $keyword) {
                if (str_contains($lowerText, strtolower($keyword))) return $service;
            }
        }
        return null;
    }

    private function findAvailableOfficer(?int $serviceId): ?User
    {
        $query = User::where('role', 'officer')->where('is_online', true)->where('is_available', true)->whereColumn('current_chat_count', '<', 'max_concurrent_chats');
        if ($serviceId) return (clone $query)->where('service_id', $serviceId)->orderBy('current_chat_count')->first();
        return $query->orderBy('current_chat_count')->first();
    }

    private function storeMessage(ChatSession $session, string $senderType, string $content, ?int $userId = null): Message
    {
        return Message::create(['chat_session_id' => $session->id, 'sender_type' => $senderType, 'sender_user_id' => $userId, 'content' => $content]);
    }

    private function getDefaultResponse(): array
    {
        return ['reply' => "Maaf, terjadi kesalahan. Silakan ketik *menu* untuk memulai.", 'action' => 'bot_reply', 'session_id' => null];
    }
}
