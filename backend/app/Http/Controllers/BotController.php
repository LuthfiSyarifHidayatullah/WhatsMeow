<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function __construct(
        private ChatbotService $chatbotService
    ) {}

    /**
     * Handle incoming message from WhatsApp bot (Go service)
     */
    public function incoming(Request $request): JsonResponse
    {
        $request->validate([
            'sender' => 'required|string',
            'chat_jid' => 'required|string',
            'text' => 'required|string',
        ]);

        $result = $this->chatbotService->processIncomingMessage(
            $request->sender,
            $request->chat_jid,
            $request->text,
        );

        return response()->json($result);
    }

    /**
     * Handle message status update from bot
     */
    public function messageStatus(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'message' => 'required|string',
            'status' => 'required|string',
        ]);

        return response()->json(['success' => true]);
    }
}
