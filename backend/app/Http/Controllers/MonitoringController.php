<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\Message;
use App\Models\Service;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function dashboard(): JsonResponse
    {
        $today = now()->startOfDay();

        $stats = [
            'total_sessions_today' => ChatSession::where('created_at', '>=', $today)->count(),
            'active_sessions' => ChatSession::where('status', 'active')->count(),
            'waiting_sessions' => ChatSession::where('status', 'waiting')->count(),
            'bot_sessions' => ChatSession::where('status', 'bot')->count(),
            'resolved_today' => ChatSession::where('status', 'resolved')
                ->where('resolved_at', '>=', $today)->count(),
            'avg_response_time' => $this->getAvgResponseTime(),
            'avg_satisfaction' => ChatSession::whereNotNull('satisfaction_rating')
                ->where('resolved_at', '>=', $today)
                ->avg('satisfaction_rating'),
            'online_officers' => User::where('is_online', true)
                ->where('role', 'officer')->count(),
            'total_officers' => User::where('role', 'officer')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get service-level statistics
     */
    public function serviceStats(): JsonResponse
    {
        $today = now()->startOfDay();

        $services = Service::withCount([
            'chatSessions as total_today' => fn($q) => $q->where('created_at', '>=', $today),
            'chatSessions as active_count' => fn($q) => $q->where('status', 'active'),
            'chatSessions as waiting_count' => fn($q) => $q->where('status', 'waiting'),
            'chatSessions as resolved_today' => fn($q) => $q->where('status', 'resolved')
                ->where('resolved_at', '>=', $today),
        ])->with(['officers' => fn($q) => $q->select('id', 'name', 'is_online', 'current_chat_count', 'service_id')])
            ->get();

        return response()->json($services);
    }

    /**
     * Get officer performance metrics
     */
    public function officerPerformance(Request $request): JsonResponse
    {
        $today = now()->startOfDay();

        $officers = User::where('role', 'officer')
            ->withCount([
                'assignedSessions as handled_today' => fn($q) => $q->where('assigned_at', '>=', $today),
                'assignedSessions as resolved_today' => fn($q) => $q->where('status', 'resolved')
                    ->where('resolved_at', '>=', $today),
                'activeSessions as active_chats',
            ])
            ->with('service:id,name')
            ->get()
            ->map(function ($officer) use ($today) {
                $avgSatisfaction = ChatSession::where('officer_id', $officer->id)
                    ->whereNotNull('satisfaction_rating')
                    ->where('resolved_at', '>=', $today)
                    ->avg('satisfaction_rating');

                return [
                    'id' => $officer->id,
                    'name' => $officer->name,
                    'service' => $officer->service?->name ?? 'Umum',
                    'is_online' => $officer->is_online,
                    'is_available' => $officer->is_available,
                    'active_chats' => $officer->active_chats,
                    'handled_today' => $officer->handled_today,
                    'resolved_today' => $officer->resolved_today,
                    'avg_satisfaction' => round($avgSatisfaction ?? 0, 1),
                ];
            });

        return response()->json($officers);
    }

    /**
     * Get real-time queue status
     */
    public function queueStatus(): JsonResponse
    {
        $waitingSessions = ChatSession::with(['service', 'latestMessage'])
            ->where('status', 'waiting')
            ->orderBy('escalated_at')
            ->get()
            ->map(fn($s) => [
                'session_id' => $s->session_id,
                'visitor_phone' => $s->visitor_phone,
                'visitor_name' => $s->visitor_name,
                'service' => $s->service?->name,
                'topic' => $s->topic,
                'waiting_since' => $s->escalated_at?->diffForHumans(),
                'waiting_minutes' => $s->escalated_at?->diffInMinutes(now()),
                'last_message' => $s->latestMessage?->content,
            ]);

        return response()->json($waitingSessions);
    }

    /**
     * Get activity logs
     */
    public function activityLogs(Request $request): JsonResponse
    {
        $logs = ActivityLog::with(['user:id,name,role', 'chatSession:id,session_id,visitor_phone'])
            ->latest()
            ->paginate(50);

        return response()->json($logs);
    }

    /**
     * Calculate average response time (in minutes)
     * Compatible with both MySQL and SQLite
     */
    private function getAvgResponseTime(): float
    {
        $today = now()->startOfDay();

        $sessions = ChatSession::where('status', 'resolved')
            ->where('resolved_at', '>=', $today)
            ->whereNotNull('escalated_at')
            ->whereNotNull('assigned_at')
            ->get(['escalated_at', 'assigned_at']);

        if ($sessions->isEmpty()) {
            return 0;
        }

        $totalMinutes = $sessions->sum(function ($session) {
            return $session->escalated_at->diffInMinutes($session->assigned_at);
        });

        return round($totalMinutes / $sessions->count(), 1);
    }
}
