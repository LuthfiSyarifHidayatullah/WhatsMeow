<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\Message;
use App\Models\ActivityLog;
use App\Services\WhatsAppBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Send notification to visitor via WhatsApp
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'chat_jid' => 'required|string',
            'visitor_phone' => 'required|string',
            'message' => 'required|string|max:4096',
            'include_rating' => 'nullable|boolean',
        ]);

        // Append rating request if included
        $messageToSend = $request->message;
        if ($request->get('include_rating', true)) {
            $messageToSend .= "\n\n---\n";
            $messageToSend .= "Mohon berikan rating layanan kami (1-5):\n";
            $messageToSend .= "1 ⭐ - Sangat Buruk\n";
            $messageToSend .= "2 ⭐⭐ - Buruk\n";
            $messageToSend .= "3 ⭐⭐⭐ - Cukup\n";
            $messageToSend .= "4 ⭐⭐⭐⭐ - Baik\n";
            $messageToSend .= "5 ⭐⭐⭐⭐⭐ - Sangat Baik";
        }

        $botService = new WhatsAppBotService();
        $success = $botService->sendMessage($request->chat_jid, $messageToSend);

        if (!$success) {
            return response()->json(['message' => 'Gagal mengirim notifikasi. Bot mungkin tidak aktif.'], 500);
        }

        // Mark the latest resolved session for this visitor as awaiting rating
        if ($request->get('include_rating', true)) {
            $session = ChatSession::where('visitor_phone', $request->visitor_phone)
                ->where('status', 'resolved')
                ->whereNull('satisfaction_rating')
                ->latest()
                ->first();

            if ($session) {
                // Reset resolved_at to now so the 5-minute rating window starts fresh
                $session->update(['resolved_at' => now()]);
            }
        }

        // Log activity
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'send_notification',
            'description' => "Notifikasi ke {$request->visitor_phone}: " . mb_substr($request->message, 0, 100),
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Notifikasi berhasil dikirim.']);
    }

    /**
     * Get list of visitors (from recent chat sessions) for notification target
     */
    public function visitors(Request $request): JsonResponse
    {
        $query = ChatSession::select('visitor_phone', 'chat_jid', 'visitor_name')
            ->whereNotNull('chat_jid')
            ->where('chat_jid', '!=', '');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('visitor_phone', 'like', "%{$search}%")
                  ->orWhere('visitor_name', 'like', "%{$search}%");
            });
        }

        $visitors = $query->groupBy('visitor_phone', 'chat_jid', 'visitor_name')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($v) => [
                'visitor_phone' => $v->visitor_phone,
                'chat_jid' => $v->chat_jid,
                'visitor_name' => $v->visitor_name,
            ]);

        return response()->json($visitors);
    }

    /**
     * Get notification templates
     */
    public function templates(): JsonResponse
    {
        $templates = [
            ['id' => 'domain_done', 'label' => 'Domain Sudah Aktif', 'message' => "Halo! Domain yang Anda ajukan sudah aktif dan siap digunakan.\n\nSilakan hubungi kami jika ada kendala.\nTerima kasih. 🙏"],
            ['id' => 'zoom_confirmed', 'label' => 'Jadwal Zoom Dikonfirmasi', 'message' => "Halo! Jadwal Zoom Meeting yang Anda ajukan sudah dikonfirmasi.\n\nLink meeting akan dikirimkan H-1 sebelum kegiatan.\nTerima kasih. 🙏"],
            ['id' => 'doc_approved', 'label' => 'Fasilitasi Dokumentasi Disetujui', 'message' => "Halo! Permohonan fasilitasi dokumentasi kegiatan Anda sudah disetujui.\n\nTim dokumentasi akan hadir sesuai jadwal yang telah ditentukan.\nTerima kasih. 🙏"],
            ['id' => 'tte_done', 'label' => 'TTE Sudah Aktif', 'message' => "Halo! Tanda Tangan Elektronik (TTE) Anda sudah aktif dan siap digunakan.\n\nSilakan hubungi kami jika ada kendala.\nTerima kasih. 🙏"],
            ['id' => 'alat_ready', 'label' => 'Alat Siap Diambil', 'message' => "Halo! Alat yang Anda pinjam sudah disiapkan.\n\nSilakan ambil di kantor Diskominfo sesuai jadwal yang telah ditentukan.\nTerima kasih. 🙏"],
            ['id' => 'rejected', 'label' => 'Pengajuan Ditolak', 'message' => "Halo! Mohon maaf, pengajuan Anda belum dapat kami proses saat ini.\n\nSilakan hubungi petugas untuk informasi lebih lanjut.\nTerima kasih. 🙏"],
        ];

        return response()->json($templates);
    }
}
