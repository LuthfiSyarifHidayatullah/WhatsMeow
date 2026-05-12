<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service to communicate with the Go WhatsApp bot webhook server.
 * Used by officers to send messages back to visitors via WhatsApp.
 */
class WhatsAppBotService
{
    private string $botUrl;
    private string $botToken;

    public function __construct()
    {
        $this->botUrl = config('services.bot.webhook_url', 'http://localhost:8080');
        $this->botToken = config('services.bot.token');
    }

    /**
     * Send message to visitor via WhatsApp bot
     */
    public function sendMessage(string $chatJID, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->botToken,
                'Content-Type' => 'application/json',
            ])->post($this->botUrl . '/send', [
                'chat_jid' => $chatJID,
                'message' => $message,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Bot send message failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Bot connection error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if bot service is running
     */
    public function healthCheck(): bool
    {
        try {
            $response = Http::get($this->botUrl . '/health');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
