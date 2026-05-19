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
     * FIX #1: Session history with date filter
     */
    public function sessionHistory(Request $request): JsonResponse
    {
        $query = ChatSession::with(['service:id,name', 'officer:id,name'])
            ->where('status', 'resolved');

        // Date filters
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }
        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }
        if ($request->has('officer_id')) {
            $query->where('officer_id', $request->officer_id);
        }

        $sessions = $query->latest()->paginate(30);

        return response()->json($sessions);
    }

    /**
     * FIX #2: Rating details per officer and per service
     */
    public function ratingDetails(Request $request): JsonResponse
    {
        // Rating per officer
        $ratingPerOfficer = User::where('role', 'officer')
            ->with('service:id,name')
            ->get()
            ->map(function ($officer) {
                $sessions = ChatSession::where('officer_id', $officer->id)
                    ->whereNotNull('satisfaction_rating');

                return [
                    'officer_id' => $officer->id,
                    'officer_name' => $officer->name,
                    'service' => $officer->service?->name ?? 'Umum',
                    'total_rated' => $sessions->count(),
                    'avg_rating' => round($sessions->avg('satisfaction_rating') ?? 0, 1),
                    'rating_1' => (clone $sessions)->where('satisfaction_rating', 1)->count(),
                    'rating_2' => (clone $sessions)->where('satisfaction_rating', 2)->count(),
                    'rating_3' => (clone $sessions)->where('satisfaction_rating', 3)->count(),
                    'rating_4' => (clone $sessions)->where('satisfaction_rating', 4)->count(),
                    'rating_5' => (clone $sessions)->where('satisfaction_rating', 5)->count(),
                ];
            });

        // Rating per service
        $ratingPerService = Service::where('is_active', true)->get()->map(function ($service) {
            $sessions = ChatSession::where('service_id', $service->id)
                ->whereNotNull('satisfaction_rating');

            return [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'total_rated' => $sessions->count(),
                'avg_rating' => round($sessions->avg('satisfaction_rating') ?? 0, 1),
                'rating_1' => (clone $sessions)->where('satisfaction_rating', 1)->count(),
                'rating_2' => (clone $sessions)->where('satisfaction_rating', 2)->count(),
                'rating_3' => (clone $sessions)->where('satisfaction_rating', 3)->count(),
                'rating_4' => (clone $sessions)->where('satisfaction_rating', 4)->count(),
                'rating_5' => (clone $sessions)->where('satisfaction_rating', 5)->count(),
            ];
        });

        // Recent ratings with details
        $recentRatings = ChatSession::with(['service:id,name', 'officer:id,name'])
            ->whereNotNull('satisfaction_rating')
            ->latest('resolved_at')
            ->limit(50)
            ->get()
            ->map(fn($s) => [
                'session_id' => $s->session_id,
                'visitor_phone' => $s->visitor_phone,
                'service' => $s->service?->name ?? '-',
                'officer' => $s->officer?->name ?? '-',
                'rating' => $s->satisfaction_rating,
                'topic' => $s->topic,
                'resolved_at' => $s->resolved_at?->format('d M Y H:i'),
            ]);

        return response()->json([
            'per_officer' => $ratingPerOfficer,
            'per_service' => $ratingPerService,
            'recent' => $recentRatings,
        ]);
    }

    /**
     * Export report data (JSON format, frontend converts to Excel)
     * FIX #2: Support custom date range (date_from, date_to)
     */
    public function exportReport(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month'); // week, month, year, custom
        $type = $request->get('type', 'topics'); // topics, ratings
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if ($type === 'topics') {
            return $this->exportTopics($period, $dateFrom, $dateTo);
        }

        return $this->exportRatings($period, $dateFrom, $dateTo);
    }

    private function exportTopics(string $period, ?string $dateFrom = null, ?string $dateTo = null): JsonResponse
    {
        if ($dateFrom && $dateTo) {
            $startDate = \Carbon\Carbon::parse($dateFrom)->startOfDay();
            $endDate = \Carbon\Carbon::parse($dateTo)->endOfDay();
        } else {
            $startDate = match($period) {
                'week' => now()->startOfWeek(),
                'month' => now()->startOfMonth(),
                'year' => now()->startOfYear(),
                default => now()->startOfMonth(),
            };
            $endDate = now();
        }

        $topics = ChatSession::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->whereNotNull('topic')
            ->where('topic', '!=', '')
            ->select('topic', 'service_id')
            ->with('service:id,name')
            ->get()
            ->groupBy('topic')
            ->map(function ($group, $topic) {
                return [
                    'topic' => $topic,
                    'count' => $group->count(),
                    'service' => $group->first()->service?->name ?? '-',
                ];
            })
            ->sortByDesc('count')
            ->values();

        $summary = [
            'period' => $period,
            'start_date' => $startDate->format('d M Y'),
            'end_date' => $endDate->format('d M Y'),
            'total_sessions' => ChatSession::where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->count(),
            'total_resolved' => ChatSession::where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->where('status', 'resolved')->count(),
        ];

        return response()->json([
            'summary' => $summary,
            'data' => $topics,
        ]);
    }

    private function exportRatings(string $period, ?string $dateFrom = null, ?string $dateTo = null): JsonResponse
    {
        if ($dateFrom && $dateTo) {
            $startDate = \Carbon\Carbon::parse($dateFrom)->startOfDay();
            $endDate = \Carbon\Carbon::parse($dateTo)->endOfDay();
        } else {
            $startDate = match($period) {
                'week' => now()->startOfWeek(),
                'month' => now()->startOfMonth(),
                'year' => now()->startOfYear(),
                default => now()->startOfMonth(),
            };
            $endDate = now();
        }

        $ratings = ChatSession::where('resolved_at', '>=', $startDate)
            ->where('resolved_at', '<=', $endDate)
            ->whereNotNull('satisfaction_rating')
            ->where('satisfaction_rating', '>', 0)
            ->with(['service:id,name', 'officer:id,name'])
            ->latest('resolved_at')
            ->get()
            ->map(fn($s) => [
                'tanggal' => $s->resolved_at?->format('d M Y'),
                'visitor' => $s->visitor_phone,
                'layanan' => $s->service?->name ?? '-',
                'petugas' => $s->officer?->name ?? '-',
                'rating' => $s->satisfaction_rating,
                'topik' => $s->topic ?? '-',
            ]);

        $summary = [
            'period' => $period,
            'start_date' => $startDate->format('d M Y'),
            'end_date' => $endDate->format('d M Y'),
            'total_rated' => $ratings->count(),
            'avg_rating' => round($ratings->avg('rating') ?? 0, 1),
        ];

        return response()->json([
            'summary' => $summary,
            'data' => $ratings,
        ]);
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
