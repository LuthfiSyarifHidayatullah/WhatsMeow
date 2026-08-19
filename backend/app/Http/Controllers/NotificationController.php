<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\Message;
use App\Models\ActivityLog;
use App\Models\Service;
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

        // Resolve any active/bot/waiting session for this visitor
        $activeSessions = ChatSession::where('chat_jid', $request->chat_jid)
            ->whereIn('status', ['bot', 'waiting', 'active'])
            ->get();

        foreach ($activeSessions as $activeSession) {
            if ($activeSession->officer_id) {
                $officer = \App\Models\User::find($activeSession->officer_id);
                if ($officer) $officer->decrement('current_chat_count');
            }
            $activeSession->update(['status' => 'resolved', 'resolved_at' => now()]);
        }

        // Mark session as awaiting rating - use chat_jid for matching
        if ($request->get('include_rating', true)) {
            $session = ChatSession::where('chat_jid', $request->chat_jid)
                ->where('status', 'resolved')
                ->latest()
                ->first();

            if ($session) {
                $session->update([
                    'resolved_at' => now(),
                    'satisfaction_rating' => null,
                ]);
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
     * Get list of visitors grouped by service for easy selection
     */
    public function visitors(Request $request): JsonResponse
    {
        $query = ChatSession::with('service:id,name')
            ->whereNotNull('chat_jid')
            ->where('chat_jid', '!=', '');

        // Filter by service
        if ($request->has('service_id') && $request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('visitor_phone', 'like', "%{$search}%")
                  ->orWhere('visitor_name', 'like', "%{$search}%");
            });
        }

        // Get unique visitors with their latest session info
        $visitors = $query->latest()
            ->get()
            ->unique('chat_jid')
            ->take(30)
            ->map(fn($s) => [
                'visitor_phone' => $s->visitor_phone,
                'chat_jid' => $s->chat_jid,
                'visitor_name' => $s->visitor_name,
                'service_name' => $s->service?->name ?? '-',
                'service_id' => $s->service_id,
                'status' => $s->status,
                'last_contact' => $s->updated_at?->diffForHumans(),
            ])
            ->values();

        return response()->json($visitors);
    }

    /**
     * Get available services for filter dropdown
     */
    public function services(): JsonResponse
    {
        $services = Service::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']);
        return response()->json($services);
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
