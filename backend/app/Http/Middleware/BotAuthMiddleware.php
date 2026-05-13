<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BotAuthMiddleware
{
    /**
     * Authenticate requests from the Go WhatsApp bot service
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $expectedToken = env('BOT_API_TOKEN', config('services.bot.token', 'mpp-bot-secret-token-2024'));

        if (!$token || $token !== $expectedToken) {
            return response()->json(['message' => 'Unauthorized bot request'], 401);
        }

        return $next($request);
    }
}
