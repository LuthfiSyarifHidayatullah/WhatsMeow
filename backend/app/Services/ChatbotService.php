<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BotResponse;
use App\Models\ChatSession;
use App\Models\Message;
use App\Models\Opd;
use App\Models\Service;
use App\Models\ServiceMenuItem;
use App\Models\User;
use App\Events\NewMessageEvent;
use App\Events\ChatEscalatedEvent;
use Illuminate\Support\Str;

class ChatbotService
{
    /**
     * Sub-menu definitions per service code.
     *
     * Cakupan se-kabupaten: layanan dikelompokkan per OPD. Setiap layanan
     * memakai salah satu dari 2 tipe aksi:
     *   - formulir_then_escalate : tampilkan link formulir, lalu ke petugas
     *   - escalate               : langsung ke petugas
     *
     * (Tipe 'schedule' masih didukung oleh kode & tabel bookings sebagai
     *  cadangan, namun tidak dipakai di layanan OPD sample saat ini.)
     */
    private array $serviceMenus = [
        // === Dinas Pendidikan ===
        'disdik_dapodik' => [
            'title' => 'Data Dapodik',
            'items' => [
                1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
                2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
                4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'disdik_sertifikasi_guru' => [
            'title' => 'Sertifikasi Guru',
            'items' => [
                1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
                2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
                4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'disdik_tpp_guru' => [
            'title' => 'TPP Guru',
            'items' => [
                1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
                2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
                4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'disdik_revitalisasi' => [
            'title' => 'Revitalisasi Sekolah',
            'items' => [
                1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
                2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
                4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'disdik_ijazah' => [
            'title' => 'Surat Keterangan Pengganti Ijazah (Hilang/Rusak)',
            'items' => [
                1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
                2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
                4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'disdik_pindah_sekolah' => [
            'title' => 'Surat Rekomendasi Pindah Sekolah',
            'items' => [
                1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
                2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
                4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'disdik_izin_sekolah' => [
            'title' => 'Izin Pembangunan Sekolah Baru',
            'items' => [
                1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
                2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
                4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'pendidikan_pengaduan' => [
            'title' => 'Pengaduan Pelayanan Pendidikan',
            'items' => [
                1 => ['label' => 'Informasi Cara Pengaduan', 'action' => 'info', 'key' => 'info'],
                2 => ['label' => 'Sampaikan Pengaduan ke Petugas', 'action' => 'escalate'],
            ],
        ],

        // === Satuan Polisi Pamong Praja ===
        'satpolpp_pengaduan_trantibum' => [
            'title' => 'Pengaduan Gangguan Ketertiban Umum',
            'items' => [
                1 => ['label' => 'Informasi Cara Pengaduan', 'action' => 'info', 'key' => 'info'],
                2 => ['label' => 'Sampaikan Pengaduan ke Petugas', 'action' => 'escalate'],
            ],
        ],
        'satpolpp_pengamanan' => [
            'title' => 'Permohonan Bantuan Pengamanan Kegiatan',
            'items' => [
                1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
                2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
                4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'satpolpp_linmas' => [
            'title' => 'Informasi Perlindungan Masyarakat (Linmas)',
            'items' => [
                1 => ['label' => 'Informasi Linmas', 'action' => 'info', 'key' => 'info'],
                2 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'satpolpp_pengaduan' => [
            'title' => 'Pengaduan Pelayanan Satpol PP',
            'items' => [
                1 => ['label' => 'Informasi Cara Pengaduan', 'action' => 'info', 'key' => 'info'],
                2 => ['label' => 'Sampaikan Pengaduan ke Petugas', 'action' => 'escalate'],
            ],
        ],

        // === Dinas Sosial ===
        'sosial_perempuan_anak' => [
            'title' => 'Pengaduan & Penanganan Kasus Perempuan dan Anak',
            'items' => [
                1 => ['label' => 'Informasi Layanan', 'action' => 'info', 'key' => 'info'],
                2 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'sosial_verval_dtks' => [
            'title' => 'Verifikasi & Validasi Data Kesejahteraan Sosial',
            'items' => [
                1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
                2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
                4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'sosial_bansos_pbijkn' => [
            'title' => 'Rekomendasi Bantuan Sosial & PBI-JKN',
            'items' => [
                1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
                2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
                4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'sosial_ppid' => [
            'title' => 'Layanan Informasi Publik (PPID)',
            'items' => [
                1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
                2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
                3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
                4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],

        // === Dinas Komunikasi dan Informatika ===
        // Tiap layanan Kominfo: "Informasi Pengajuan" (teks dari bot_responses,
        // dapat diedit di dashboard) lalu "Hubungi Petugas".
        'domain' => [
            'title' => 'Domain Bengkayangkab.go.id',
            'items' => [
                1 => ['label' => 'Informasi Pengajuan', 'action' => 'info', 'key' => 'info'],
                2 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'kominfo_permohonan_informasi' => [
            'title' => 'Permohonan Informasi',
            'items' => [
                1 => ['label' => 'Informasi Pengajuan', 'action' => 'info', 'key' => 'info'],
                2 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'tte' => [
            'title' => 'Tanda Tangan Elektronik (TTE)',
            'items' => [
                1 => ['label' => 'Informasi Pengajuan', 'action' => 'info', 'key' => 'info'],
                2 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
        'kominfo_sp4n' => [
            'title' => 'Lapor SP4N',
            'items' => [
                1 => ['label' => 'Informasi Pengajuan', 'action' => 'info', 'key' => 'info'],
                2 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
            ],
        ],
    ];

    /**
     * Resolusi definisi sub-menu untuk sebuah layanan.
     *
     * Prioritas:
     *   1. Item sub-menu dari database (tabel service_menu_items) → dapat
     *      dikelola dari dashboard.
     *   2. Fallback ke definisi hard-coded $serviceMenus (untuk layanan lama
     *      yang belum dimigrasikan).
     *
     * Mengembalikan array berbentuk:
     *   ['items' => [ <position> => ['label','action','response_text'?], ... ]]
     * atau null jika layanan tidak punya sub-menu sama sekali.
     */
    private function resolveMenuDef(Service $service): ?array
    {
        $dbItems = ServiceMenuItem::where('service_id', $service->id)
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        if ($dbItems->isNotEmpty()) {
            $items = [];
            foreach ($dbItems as $row) {
                $items[(int) $row->position] = [
                    'label' => $row->label,
                    'action' => $row->action,
                    'response_text' => $row->response_text,
                ];
            }
            return ['items' => $items];
        }

        // Fallback ke definisi lama (hard-coded).
        return $this->serviceMenus[$service->code] ?? null;
    }

    /**
     * Ambil teks respons untuk item info/formulir.
     *
     * Jika item berasal dari DB dan punya response_text, pakai itu. Jika tidak,
     * fallback ke tabel bot_responses berdasarkan 'key' (perilaku lama).
     * Mengembalikan null jika tidak ada teks yang tersedia.
     */
    private function resolveItemResponseText(Service $service, array $item): ?string
    {
        if (!empty($item['response_text'])) {
            return $item['response_text'];
        }

        $key = $item['key'] ?? null;
        if (!$key) {
            return null;
        }

        $botResponse = BotResponse::where('service_id', $service->id)
            ->where('trigger_keyword', $key)
            ->where('is_active', true)
            ->first();

        return $botResponse?->response_text;
    }

    /**
     * Process incoming message from WhatsApp bot
     */
    public function processIncomingMessage(string $sender, string $chatJID, string $text): array
    {
        // Normalisasi nomor pengirim agar sesi dikenali konsisten walau JID
        // membawa sufiks perangkat (mis. "6281xxxx:12@s.whatsapp.net" saat
        // pengguna berpindah HP/WhatsApp Web). Tanpa ini, sesi active bisa
        // "hilang" dan pengguna malah dikirimi menu lagi.
        $sender = $this->normalizePhone($sender);

        $this->expireRatingWindow($sender);

        $ratingResult = $this->handleRatingIfApplicable($sender, $text);
        if ($ratingResult) {
            return $ratingResult;
        }

        $session = $this->getOrCreateSession($sender, $chatJID);

        // Refresh session from database (officer might have accepted/changed status since last message)
        $session->refresh();

        // NOTE: Timeout tidak dicek di sini. Pesan masuk dari visitor = visitor aktif,
        // jadi tidak boleh di-timeout. Timeout hanya ditangani oleh scheduler
        // (command chat:check-timeout yang jalan setiap menit).

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

        // Menu utama (reset ke pemilihan OPD)
        if (in_array($lowerText, ['menu', '0', 'halo', 'hai', 'hi', 'hello', 'start'])) {
            $session->update(['service_id' => null, 'current_opd_id' => null, 'topic' => null]);
            return $this->getMainMenu($session);
        }

        // Back command (9)
        if ($lowerText === '9') {
            // Sudah pilih layanan → kembali ke menu OPD
            if ($session->service_id) {
                $session->update(['service_id' => null]);
                return $this->getOpdMenu($session);
            }
            // Sudah pilih OPD (belum pilih layanan) → kembali ke menu utama
            if ($session->current_opd_id) {
                $session->update(['current_opd_id' => null]);
                return $this->getMainMenu($session);
            }
            return $this->getMainMenu($session);
        }

        // Exit
        if (in_array($lowerText, ['selesai', 'done', 'keluar', 'exit'])) {
            return $this->resolveSession($session);
        }

        // Direct escalation (hanya jika layanan sudah dipilih, agar petugas tepat)
        if (in_array($lowerText, ['petugas', 'operator', 'live chat', 'konfirmasi'])) {
            if ($session->service_id) {
                return $this->escalateToOfficer($session, $session->service_id);
            }
            // Belum pilih layanan → arahkan pilih dulu
            if ($session->current_opd_id) {
                return $this->getOpdMenu($session);
            }
            return $this->getMainMenu($session);
        }

        // Numeric input
        if (is_numeric($lowerText)) {
            $number = (int) $lowerText;

            // Layanan sudah dipilih → sub-menu layanan
            if ($session->service_id) {
                return $this->handleSubMenuSelection($session, $number);
            }

            // OPD sudah dipilih → pilih layanan dalam OPD
            if ($session->current_opd_id) {
                return $this->handleOpdMenuSelection($session, $number);
            }

            // Belum pilih apa-apa → pilih OPD dari menu utama
            return $this->handleMainMenuSelection($session, $number);
        }

        // Keyword matching (lintas OPD) → langsung ke sub-menu layanan
        $matchedService = $this->matchServiceByKeywords($text);
        if ($matchedService) {
            $session->update([
                'service_id' => $matchedService->id,
                'current_opd_id' => $matchedService->opd_id,
                'topic' => $text,
            ]);
            return $this->getServiceSubMenu($session);
        }

        // Tidak dikenali → tampilkan menu utama
        return $this->getMainMenu($session);
    }

    /**
     * Handle main menu selection = pilih OPD (Instansi)
     */
    private function handleMainMenuSelection(ChatSession $session, int $number): array
    {
        $opds = Opd::where('is_active', true)->orderBy('sort_order')->get();

        if ($number > 0 && $number <= $opds->count()) {
            $opd = $opds[$number - 1];
            $session->update(['current_opd_id' => $opd->id, 'service_id' => null]);
            return $this->getOpdMenu($session);
        }

        // Opsi terakhir (jumlah OPD + 1) = Pengaduan umum → ke admin/supervisor
        if ($number === $opds->count() + 1) {
            return $this->escalateToAdmin($session);
        }

        return $this->getMainMenu($session);
    }

    /**
     * Handle OPD menu selection = pilih layanan di dalam OPD
     */
    private function handleOpdMenuSelection(ChatSession $session, int $number): array
    {
        $opd = Opd::find($session->current_opd_id);
        if (!$opd) {
            $session->update(['current_opd_id' => null]);
            return $this->getMainMenu($session);
        }

        $services = Service::where('opd_id', $opd->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($number > 0 && $number <= $services->count()) {
            $service = $services[$number - 1];
            $session->update(['service_id' => $service->id, 'topic' => $service->name]);
            return $this->getServiceSubMenu($session);
        }

        return $this->getOpdMenu($session);
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

        $menuDef = $this->resolveMenuDef($service);

        // Fallback: layanan tanpa definisi menu → angka 1 = hubungi petugas
        if (!$menuDef) {
            if ($number === 1) {
                return $this->escalateToOfficer($session, $session->service_id);
            }
            return $this->getServiceSubMenu($session);
        }

        // Angka 8 = pintasan "Hubungi Petugas" yang seragam di semua layar
        // info/formulir/jadwal (lihat navFooter()). Hanya berlaku bila layanan
        // tidak memakai posisi 8 sebagai item menu sungguhan.
        if ($number === 8 && !isset($menuDef['items'][8])) {
            return $this->escalateToOfficer($session, $session->service_id);
        }

        if (!isset($menuDef['items'][$number])) {
            // Angka di luar daftar → tampilkan ulang sub-menu.
            // (Konfirmasi setelah isi formulir ditangani via keyword "konfirmasi"
            //  di handleBotMode, bukan angka, agar tidak bentrok dengan nomor menu.)
            return $this->getServiceSubMenu($session);
        }

        $item = $menuDef['items'][$number];

        // Escalate to officer
        if ($item['action'] === 'escalate') {
            return $this->escalateToOfficer($session, $session->service_id);
        }

        // Show formulir link then escalate to officer
        if ($item['action'] === 'formulir_then_escalate') {
            return $this->showFormulirThenEscalate($session, $service, $item);
        }

        // Show real-time schedule from bookings table
        if ($item['action'] === 'schedule') {
            return $this->showSchedule($session, $service);
        }

        // Show info from bot_responses
        return $this->showSubMenuInfo($session, $service, $item);
    }

    /**
     * Footer navigasi standar untuk layar info/formulir/jadwal.
     *
     * Seragam di semua layanan agar warga mudah mengingat:
     *   8 = Hubungi Petugas
     *   9 = Kembali (pilih layanan lain)
     *   0 = Menu Utama
     *
     * Angka 8 ditangani di handleSubMenuSelection() sebagai eskalasi ke petugas,
     * terlepas dari nomor menu "Hubungi Petugas" pada masing-masing layanan.
     */
    private function navFooter(): string
    {
        $footer = "\n\n---\n";
        $footer .= "Ketik angka:\n";
        $footer .= "8. Hubungi Petugas\n";
        $footer .= "9. Kembali (pilih layanan lain)\n";
        $footer .= "0. Menu Utama";
        return $footer;
    }

    /**
     * Show formulir link, then wait for visitor to confirm before escalating
     */
    private function showFormulirThenEscalate(ChatSession $session, Service $service, array $item): array
    {
        $responseText = $this->resolveItemResponseText($service, $item);

        if ($responseText) {
            $reply = $responseText;
        } else {
            $reply = "📝 *Formulir Pengajuan {$service->name}*\n\n";
            $reply .= "Silakan isi formulir pengajuan. Link formulir belum tersedia.\n";
            $reply .= "Silakan hubungi petugas untuk informasi lebih lanjut.";
        }

        $reply .= "\n\n---\n";
        $reply .= "Konfirmasi ke petugas bersifat *opsional*. Jika setelah mengisi formulir Anda ingin terhubung dengan petugas, ketik *konfirmasi*.";
        $reply .= $this->navFooter();

        $this->storeMessage($session, 'bot', $reply);
        return [
            'reply' => $reply,
            'action' => 'bot_reply',
            'session_id' => $session->session_id,
            'service_id' => $service->id,
        ];
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

        $reply .= $this->navFooter();

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
        $responseText = $this->resolveItemResponseText($service, $item);

        if ($responseText) {
            $reply = $responseText;
        } else {
            $reply = "ℹ️ *{$item['label']}*\n\n";
            $reply .= "Informasi untuk {$item['label']} layanan {$service->name} belum tersedia.\n";
            $reply .= "Silakan hubungi petugas untuk informasi lebih lanjut.";
        }

        // Setelah informasi, tampilkan navigasi standar (8/9/0).
        $reply .= $this->navFooter();

        $this->storeMessage($session, 'bot', $reply);
        return [
            'reply' => $reply,
            'action' => 'bot_reply',
            'session_id' => $session->session_id,
            'service_id' => $service->id,
        ];
    }

    /**
     * Get main menu = daftar OPD (Instansi)
     */
    private function getMainMenu(?ChatSession $session = null): array
    {
        $opds = Opd::where('is_active', true)->orderBy('sort_order')->get();

        $reply = "🙏 *Selamat datang di Layanan Informasi*\n";
        $reply .= "*PEMERINTAH KABUPATEN BENGKAYANG* 🏛️\n\n";
        $reply .= "Senang bisa membantu Anda hari ini. Kami siap melayani kebutuhan informasi dan layanan Anda. 😊\n\n";
        $reply .= "Silakan pilih instansi/perangkat daerah yang Anda tuju:\n\n";

        foreach ($opds as $index => $opd) {
            $reply .= ($index + 1) . ". {$opd->name}\n";
        }

        // Opsi Pengaduan umum di akhir daftar (nomor = jumlah OPD + 1).
        // Langsung diteruskan ke admin/supervisor tanpa memilih instansi.
        $pengaduanNumber = $opds->count() + 1;
        $reply .= "{$pengaduanNumber}. 📢 Pengaduan\n";

        $reply .= "\nKetik angka sesuai pilihan Anda.";

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
     * Get OPD menu = daftar layanan dalam OPD terpilih
     */
    private function getOpdMenu(ChatSession $session): array
    {
        $opd = Opd::find($session->current_opd_id);
        if (!$opd) {
            $session->update(['current_opd_id' => null]);
            return $this->getMainMenu($session);
        }

        $services = Service::where('opd_id', $opd->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $reply = "🏛️ *{$opd->name}*\n\n";
        $reply .= "Silakan pilih pelayanan:\n\n";

        foreach ($services as $index => $service) {
            $reply .= ($index + 1) . ". {$service->name}\n";
        }

        $reply .= "\n9. Kembali (pilih instansi lain)\n";
        $reply .= "0. Menu Utama";

        $this->storeMessage($session, 'bot', $reply);
        return [
            'reply' => $reply,
            'action' => 'bot_reply',
            'session_id' => $session->session_id,
            'opd_id' => $opd->id,
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

        $menuDef = $this->resolveMenuDef($service);

        $reply = "📋 *{$service->name}*\n\n";
        $reply .= "Pilih informasi yang dibutuhkan:\n\n";

        if ($menuDef) {
            foreach ($menuDef['items'] as $num => $item) {
                $reply .= "{$num}. {$item['label']}\n";
            }
        } else {
            $reply .= "1. Hubungi Petugas\n";
        }

        $reply .= "\n9. Kembali (pilih layanan lain)\n";
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

        $reply = "🙏 Terima kasih sudah menghubungi kami.\n\n";
        $reply .= "Saat ini seluruh petugas sedang melayani, jadi mohon menunggu sebentar ya. Permintaan Anda sudah masuk antrian dan petugas akan segera membalas secepatnya. ⏳\n\n";
        $reply .= "Agar lebih cepat ditangani, silakan tuliskan dulu pertanyaan atau kebutuhan Anda di sini. Pesan Anda pasti kami baca. 😊";

        $this->storeMessage($session, 'bot', $reply);

        return [
            'reply' => $reply,
            'action' => 'waiting',
            'session_id' => $session->session_id,
            'service_id' => $session->service_id,
        ];
    }

    /**
     * Escalate pengaduan umum ke admin/supervisor (tanpa terikat OPD/layanan).
     * Dipakai untuk opsi "Pengaduan" di menu utama.
     */
    private function escalateToAdmin(ChatSession $session): array
    {
        // Tandai sebagai pengaduan umum; tidak terikat service tertentu.
        $session->update([
            'service_id' => null,
            'current_opd_id' => null,
            'topic' => 'Pengaduan Umum',
        ]);

        $admin = $this->findAvailableAdmin();

        if ($admin) {
            $session->update([
                'status' => 'active',
                'officer_id' => $admin->id,
                'escalated_at' => now(),
                'assigned_at' => now(),
            ]);
            $admin->increment('current_chat_count');

            $reply = "✅ Anda telah terhubung dengan petugas pengaduan kami.\n\n";
            $reply .= "👤 *{$admin->name}*\n";
            $reply .= "📌 Layanan Pengaduan\n\n";
            $reply .= "Silakan sampaikan pengaduan Anda secara jelas.\n";
            $reply .= "Ketik *selesai* jika sudah selesai.";

            $this->storeMessage($session, 'bot', $reply);
            event(new ChatEscalatedEvent($session));

            return [
                'reply' => $reply,
                'action' => 'escalate',
                'session_id' => $session->session_id,
                'service_id' => null,
                'officer_id' => $admin->id,
            ];
        }

        // Tidak ada admin/supervisor tersedia → masuk antrian
        $session->update([
            'status' => 'waiting',
            'escalated_at' => now(),
        ]);

        $reply = "🙏 Terima kasih telah menyampaikan pengaduan kepada kami.\n\n";
        $reply .= "Pengaduan Anda sudah kami terima dan akan segera diproses. ✅\n\n";
        $reply .= "Silakan tuliskan pengaduan Anda selengkap mungkin di sini agar lebih mudah kami tindak lanjuti. 😊";

        $this->storeMessage($session, 'bot', $reply);

        return [
            'reply' => $reply,
            'action' => 'waiting',
            'session_id' => $session->session_id,
            'service_id' => null,
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
        $session->update(['status' => 'resolved', 'resolved_at' => now(), 'current_opd_id' => null]);
        if ($session->officer_id) {
            $officer = User::find($session->officer_id);
            if ($officer) $officer->decrement('current_chat_count');
        }

        $reply = "✅ Terima kasih telah menghubungi Pemerintah Kabupaten Bengkayang! 🙏\n\n";
        $reply .= "Pengajuan Anda sedang diproses. Kami akan memberitahu setelah selesai.\n";
        $reply .= "Ketik *menu* untuk memulai percakapan baru.";

        $this->storeMessage($session, 'bot', $reply);
        return ['reply' => $reply, 'action' => 'resolved', 'session_id' => $session->session_id];
    }

    /**
     * Normalisasi nomor/JID pengirim menjadi nomor telepon murni (hanya digit).
     * Membuang sufiks perangkat (":12"), domain ("@s.whatsapp.net"), dan
     * karakter non-digit lain. Contoh:
     *   "6281234567890:12@s.whatsapp.net" -> "6281234567890"
     *   "+62 812-3456-7890"               -> "6281234567890"
     */
    private function normalizePhone(string $sender): string
    {
        // Ambil bagian sebelum '@' (buang domain) dan sebelum ':' (buang device id)
        $local = explode('@', $sender)[0];
        $local = explode(':', $local)[0];

        // Sisakan digit saja
        $digits = preg_replace('/\D+/', '', $local);

        // Fallback: jika hasil kosong, kembalikan sender asli agar tidak error
        return $digits !== '' ? $digits : $sender;
    }

    /**
     * Query dasar untuk mencocokkan sesi milik satu visitor.
     * Toleran terhadap sesi lama yang tersimpan dengan JID bersufiks:
     * cocokkan nilai persis ($phone) ATAU yang diawali nomor tsb ("$phone%").
     */
    private function sessionsForVisitor(string $phone)
    {
        return ChatSession::where(function ($q) use ($phone) {
            $q->where('visitor_phone', $phone)
              ->orWhere('visitor_phone', 'like', $phone . '%');
        });
    }

    private function expireRatingWindow(string $sender): void
    {
        $this->sessionsForVisitor($sender)
            ->where('status', 'resolved')
            ->whereNull('satisfaction_rating')
            ->where('resolved_at', '<', now()->subMinutes(30))
            ->update(['satisfaction_rating' => 0]);
    }

    private function handleRatingIfApplicable(string $sender, string $text): ?array
    {
        $lowerText = strtolower(trim($text));
        if (!in_array($lowerText, ['1', '2', '3', '4', '5'])) return null;

        $activeSession = $this->sessionsForVisitor($sender)
            ->whereIn('status', ['bot', 'waiting', 'active'])->first();
        if ($activeSession) return null;

        // Find session awaiting rating (within 30 minutes of notification being sent)
        $session = $this->sessionsForVisitor($sender)
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
        // $sender sudah dinormalisasi (nomor murni) di entry point.
        $session = $this->sessionsForVisitor($sender)
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

        // Don't timeout if session was just assigned (officer just accepted)
        if ($session->status === 'active' && $session->assigned_at && now()->diffInMinutes($session->assigned_at) < 5) {
            return false;
        }

        $lastMessage = Message::where('chat_session_id', $session->id)->latest()->first();
        if (!$lastMessage) return false;

        $minutesSinceLastMessage = now()->diffInMinutes($lastMessage->created_at);

        if ($session->status === 'active' && $minutesSinceLastMessage >= 5) {
            // Check if officer has sent any message AFTER accepting
            $lastOfficerMessage = Message::where('chat_session_id', $session->id)->where('sender_type', 'officer')->latest()->first();

            // If officer has replied recently (within 5 min), don't timeout
            if ($lastOfficerMessage && now()->diffInMinutes($lastOfficerMessage->created_at) < 5) {
                return false;
            }

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

    /**
     * Cari admin/supervisor yang online & masih punya kapasitas chat.
     * Dipakai untuk menangani pengaduan umum dari menu utama.
     */
    private function findAvailableAdmin(): ?User
    {
        return User::whereIn('role', ['admin', 'supervisor'])
            ->where('is_online', true)
            ->where('is_available', true)
            ->whereColumn('current_chat_count', '<', 'max_concurrent_chats')
            ->orderBy('current_chat_count')
            ->first();
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

    /**
     * Process incoming media (image/document) from visitor
     */
    public function processIncomingMedia(string $sender, string $chatJID, string $mediaType, string $caption, string $mediaUrl): array
    {
        $sender = $this->normalizePhone($sender);
        $session = $this->getOrCreateSession($sender, $chatJID);
        $session->refresh();

        // Store media message
        $content = $caption ?: ($mediaType === 'image' ? '[Gambar]' : '[Dokumen]');
        Message::create([
            'chat_session_id' => $session->id,
            'sender_type' => 'visitor',
            'content' => $content,
            'content_type' => $mediaType,
            'media_url' => $mediaUrl,
        ]);

        // If session is active or waiting, forward to officer (broadcast for real-time)
        if (in_array($session->status, ['active', 'waiting'])) {
            event(new NewMessageEvent($session, $content, 'visitor'));
            return ['reply' => '', 'action' => 'forward_to_officer', 'session_id' => $session->session_id];
        }

        // If in bot mode, ask visitor to choose a service first
        $reply = "Terima kasih, file Anda sudah kami terima. 📎\n\n";
        $reply .= "Namun, silakan pilih layanan terlebih dahulu atau ketik *petugas* untuk terhubung dengan petugas kami.";
        $this->storeMessage($session, 'bot', $reply);

        return ['reply' => $reply, 'action' => 'bot_reply', 'session_id' => $session->session_id];
    }
}
