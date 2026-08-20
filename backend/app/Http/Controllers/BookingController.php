<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with(['service:id,name', 'creator:id,name']);

        // Officer hanya lihat jadwal layanannya
        $user = $request->user();
        if ($user->role === 'officer' && $user->service_id) {
            $query->where('service_id', $user->service_id);
        } elseif ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->has('location')) $query->where('location', $request->location);
        if ($request->has('date_from')) $query->where('date', '>=', $request->date_from);
        if ($request->has('date_to')) $query->where('date', '<=', $request->date_to);
        $query->where('status', $request->get('status', 'confirmed'));
        return response()->json($query->orderBy('date')->orderBy('start_time')->paginate(30));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'title' => 'required|string|max:255',
            'booked_by' => 'required|string|max:255',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'required|string',
        ]);

        // Officer hanya bisa tambah jadwal untuk layanannya sendiri
        $user = $request->user();
        if ($user->role === 'officer' && $user->service_id && (int)$request->service_id !== (int)$user->service_id) {
            return response()->json(['message' => 'Anda hanya bisa menambah jadwal untuk layanan Anda sendiri.'], 403);
        }

        // Parse time to H:i format (handle both 24h and 12h formats)
        $startTime = date('H:i', strtotime($request->start_time));
        $endTime = date('H:i', strtotime($request->end_time));

        if ($endTime <= $startTime) {
            return response()->json(['message' => 'Jam selesai harus setelah jam mulai.'], 422);
        }

        // Check conflict
        $conflict = Booking::where('date', $request->date)
            ->where('location', $request->location)->where('status', 'confirmed')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
            })->exists();

        if ($conflict) {
            return response()->json(['message' => 'Jadwal bentrok! Sudah ada kegiatan lain di ruangan dan waktu tersebut.'], 422);
        }

        $booking = Booking::create([
            ...$request->only(['service_id', 'title', 'booked_by', 'pic_name', 'pic_phone', 'date', 'location', 'notes']),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Jadwal berhasil ditambahkan.', 'booking' => $booking->load(['service:id,name'])], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Booking::with(['service:id,name', 'creator:id,name'])->findOrFail($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i',
            'location' => 'sometimes|string|in:Media Center,Podcast',
            'status' => 'sometimes|string|in:confirmed,cancelled',
        ]);
        $booking->update($request->only(['title', 'booked_by', 'pic_name', 'pic_phone', 'date', 'start_time', 'end_time', 'location', 'status', 'notes']));
        return response()->json(['message' => 'Jadwal berhasil diupdate.', 'booking' => $booking->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        Booking::findOrFail($id)->delete();
        return response()->json(['message' => 'Jadwal berhasil dihapus.']);
    }
}
