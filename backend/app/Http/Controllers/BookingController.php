<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * List bookings with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with(['service:id,name', 'creator:id,name']);

        // Filter by service
        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by location
        if ($request->has('location')) {
            $query->where('location', $request->location);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: only show confirmed
            $query->where('status', 'confirmed');
        }

        $bookings = $query->orderBy('date')->orderBy('start_time')->paginate(30);

        return response()->json($bookings);
    }

    /**
     * Create a new booking
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'title' => 'required|string|max:255',
            'booked_by' => 'required|string|max:255',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:50',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|in:Media Center,Podcast',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check for schedule conflict
        $conflict = Booking::where('date', $request->date)
            ->where('location', $request->location)
            ->where('status', 'confirmed')
            ->where(function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>', $request->start_time);
                });
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Jadwal bentrok! Sudah ada kegiatan lain di ruangan dan waktu tersebut.',
            ], 422);
        }

        $booking = Booking::create([
            ...$request->only(['service_id', 'title', 'booked_by', 'pic_name', 'pic_phone', 'date', 'start_time', 'end_time', 'location', 'notes']),
            'created_by' => $request->user()->id,
            'status' => 'confirmed',
        ]);

        return response()->json([
            'message' => 'Jadwal berhasil ditambahkan.',
            'booking' => $booking->load(['service:id,name', 'creator:id,name']),
        ], 201);
    }

    /**
     * Show a single booking
     */
    public function show(int $id): JsonResponse
    {
        $booking = Booking::with(['service:id,name', 'creator:id,name'])->findOrFail($id);
        return response()->json($booking);
    }

    /**
     * Update a booking
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'booked_by' => 'sometimes|string|max:255',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:50',
            'date' => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i',
            'location' => 'sometimes|string|in:Media Center,Podcast',
            'status' => 'sometimes|string|in:confirmed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check conflict if date/time/location changed
        if ($request->hasAny(['date', 'start_time', 'end_time', 'location'])) {
            $date = $request->get('date', $booking->date->format('Y-m-d'));
            $startTime = $request->get('start_time', $booking->start_time);
            $endTime = $request->get('end_time', $booking->end_time);
            $location = $request->get('location', $booking->location);

            $conflict = Booking::where('id', '!=', $booking->id)
                ->where('date', $date)
                ->where('location', $location)
                ->where('status', 'confirmed')
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                })
                ->exists();

            if ($conflict) {
                return response()->json([
                    'message' => 'Jadwal bentrok! Sudah ada kegiatan lain di ruangan dan waktu tersebut.',
                ], 422);
            }
        }

        $booking->update($request->only(['title', 'booked_by', 'pic_name', 'pic_phone', 'date', 'start_time', 'end_time', 'location', 'status', 'notes']));

        return response()->json([
            'message' => 'Jadwal berhasil diupdate.',
            'booking' => $booking->fresh()->load(['service:id,name', 'creator:id,name']),
        ]);
    }

    /**
     * Delete a booking
     */
    public function destroy(int $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return response()->json(['message' => 'Jadwal berhasil dihapus.']);
    }

    /**
     * Get schedule for chatbot (public, no auth needed)
     * Returns upcoming 30 days of confirmed bookings grouped by date & location
     */
    public function chatbotSchedule(Request $request): JsonResponse
    {
        $serviceId = $request->get('service_id');
        $location = $request->get('location');

        $query = Booking::confirmed()->upcoming()->with('service:id,name');

        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }
        if ($location) {
            $query->where('location', $location);
        }

        $bookings = $query->get();

        // Group by date
        $grouped = $bookings->groupBy(fn($b) => $b->date->format('Y-m-d'))
            ->map(function ($dayBookings, $date) {
                return [
                    'date' => $date,
                    'date_formatted' => \Carbon\Carbon::parse($date)->translatedFormat('d F Y'),
                    'day' => \Carbon\Carbon::parse($date)->translatedFormat('l'),
                    'bookings' => $dayBookings->map(fn($b) => [
                        'title' => $b->title,
                        'booked_by' => $b->booked_by,
                        'start_time' => substr($b->start_time, 0, 5),
                        'end_time' => substr($b->end_time, 0, 5),
                        'location' => $b->location,
                    ])->values(),
                ];
            })->values();

        return response()->json($grouped);
    }
}
