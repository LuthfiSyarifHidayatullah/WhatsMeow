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
     * Handle incoming media (image/document) from WhatsApp bot
     */
    public function incomingMedia(Request $request): JsonResponse
    {
        $request->validate([
            'sender' => 'required|string',
            'chat_jid' => 'required|string',
            'media_type' => 'required|string|in:image,document',
            'file' => 'required|file|max:20480', // max 20MB
            'caption' => 'nullable|string',
        ]);

        // Store the uploaded file
        $file = $request->file('file');
        $path = $file->store('chat-media', 'public');
        $mediaUrl = '/storage/' . $path;

        $result = $this->chatbotService->processIncomingMedia(
            $request->sender,
            $request->chat_jid,
            $request->media_type,
            $request->caption ?? '',
            $mediaUrl,
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
