<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\Message;
use App\Models\User;
use App\Events\NewMessageEvent;
use App\Services\WhatsAppBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatSessionController extends Controller
{
    /**
     * List chat sessions with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = ChatSession::with(['service', 'officer', 'latestMessage']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by service
        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by officer
        if ($request->has('officer_id')) {
            $query->where('officer_id', $request->officer_id);
        }

        // For officers, only show their assigned or service sessions
        $user = $request->user();
        if ($user->isOfficer()) {
            $query->where(function ($q) use ($user) {
                $q->where('officer_id', $user->id)
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('service_id', $user->service_id)
                            ->where('status', 'waiting');
                    });
            });
        }

        $sessions = $query->latest()->paginate(20);

        return response()->json($sessions);
    }

    /**
     * Get single session with messages
     */
    public function show(string $sessionId): JsonResponse
    {
        $session = ChatSession::with(['service', 'officer', 'messages.senderUser'])
            ->where('session_id', $sessionId)
            ->firstOrFail();

        return response()->json($session);
    }

    /**
     * Officer accepts a waiting chat
     */
    public function accept(Request $request, string $sessionId): JsonResponse
    {
        $session = ChatSession::where('session_id', $sessionId)
            ->where('status', 'waiting')
            ->firstOrFail();

        $user = $request->user();

        if (!$user->canAcceptChat()) {
            return response()->json([
                'message' => 'Anda sudah mencapai batas maksimal chat aktif.',
            ], 422);
        }

        $session->update([
            'status' => 'active',
            'officer_id' => $user->id,
            'assigned_at' => now(),
        ]);

        $user->increment('current_chat_count');

        return response()->json([
            'message' => 'Chat berhasil diterima.',
            'session' => $session->load(['service', 'officer']),
        ]);
    }

    /**
     * Transfer chat to another officer/service
     */
    public function transfer(Request $request, string $sessionId): JsonResponse
    {
        $request->validate([
            'target_officer_id' => 'nullable|exists:users,id',
            'target_service_id' => 'nullable|exists:services,id',
        ]);

        $session = ChatSession::where('session_id', $sessionId)
            ->where('status', 'active')
            ->firstOrFail();

        $currentOfficer = User::find($session->officer_id);
        if ($currentOfficer) {
            $currentOfficer->decrement('current_chat_count');
        }

        if ($request->target_officer_id) {
            $targetOfficer = User::findOrFail($request->target_officer_id);
            $session->update([
                'officer_id' => $targetOfficer->id,
                'service_id' => $request->target_service_id ?? $session->service_id,
            ]);
            $targetOfficer->increment('current_chat_count');
        } else {
            // Put back in queue for new service
            $session->update([
                'status' => 'waiting',
                'officer_id' => null,
                'service_id' => $request->target_service_id,
            ]);
        }

        return response()->json([
            'message' => 'Chat berhasil ditransfer.',
            'session' => $session->fresh()->load(['service', 'officer']),
        ]);
    }

    /**
     * Resolve/close a chat session
     */
    public function resolve(Request $request, string $sessionId): JsonResponse
    {
        $session = ChatSession::where('session_id', $sessionId)
            ->whereIn('status', ['active', 'waiting'])
            ->firstOrFail();

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

        return response()->json([
            'message' => 'Chat berhasil diselesaikan.',
            'session' => $session->fresh(),
        ]);
    }

    /**
     * Send message from officer to visitor (via WhatsApp bot)
     */
    public function sendMessage(Request $request, string $sessionId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:4096',
        ]);

        $session = ChatSession::where('session_id', $sessionId)
            ->where('status', 'active')
            ->firstOrFail();

        $user = $request->user();

        // Store message
        $message = Message::create([
            'chat_session_id' => $session->id,
            'sender_type' => 'officer',
            'sender_user_id' => $user->id,
            'content' => $request->content,
        ]);

        // Broadcast to WebSocket for real-time update
        event(new NewMessageEvent($session, $request->content, 'officer'));

        // Send via WhatsApp bot to the visitor
        $botService = new WhatsAppBotService();
        $botService->sendMessage($session->chat_jid, $request->content);

        return response()->json([
            'message' => $message->load('senderUser'),
        ]);
    }
}
