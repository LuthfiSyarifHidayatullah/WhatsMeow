<?php

namespace App\Http\Controllers;

use App\Models\InfoFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InfoFeedbackController extends Controller
{
    /**
     * Ringkasan kebermanfaatan info, dikelompokkan per layanan + label info.
     * Mengembalikan jumlah "Ya", "Tidak", total, dan persentase bermanfaat.
     */
    public function index(Request $request): JsonResponse
    {
        $query = InfoFeedback::query()
            ->select(
                'service_id',
                'info_label',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN is_helpful = 1 THEN 1 ELSE 0 END) as helpful'),
                DB::raw('SUM(CASE WHEN is_helpful = 0 THEN 1 ELSE 0 END) as not_helpful')
            )
            ->with('service:id,name')
            ->groupBy('service_id', 'info_label')
            ->orderByDesc(DB::raw('COUNT(*)'));

        // Petugas hanya melihat feedback layanannya.
        $user = $request->user();
        if ($user->isOfficer() && $user->service_id) {
            $query->where('service_id', $user->service_id);
        } elseif ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $rows = $query->get()->map(function ($r) {
            $total = (int) $r->total;
            $helpful = (int) $r->helpful;
            return [
                'service_id' => $r->service_id,
                'service_name' => $r->service?->name ?? 'Umum',
                'info_label' => $r->info_label,
                'helpful' => $helpful,
                'not_helpful' => (int) $r->not_helpful,
                'total' => $total,
                'helpful_percentage' => $total > 0 ? round($helpful / $total * 100) : 0,
            ];
        });

        // Ringkasan keseluruhan.
        $summary = [
            'total' => $rows->sum('total'),
            'helpful' => $rows->sum('helpful'),
            'not_helpful' => $rows->sum('not_helpful'),
        ];
        $summary['helpful_percentage'] = $summary['total'] > 0
            ? round($summary['helpful'] / $summary['total'] * 100)
            : 0;

        return response()->json([
            'summary' => $summary,
            'items' => $rows,
        ]);
    }
}
