<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial tidak valid.'],
            ]);
        }

        // Set user online + catat kehadiran
        $user->update(['is_online' => true, 'last_seen_at' => now()]);

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => "User {$user->name} logged in",
            'ip_address' => $request->ip(),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load('service'),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['is_online' => false]);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'logout',
            'description' => "User {$user->name} logged out",
            'ip_address' => $request->ip(),
        ]);

        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load('service'),
        ]);
    }

    /**
     * Heartbeat kehadiran: dipanggil berkala oleh dashboard selama petugas
     * membuka aplikasi. Memastikan is_online = true dan memperbarui
     * last_seen_at. Jika petugas menutup browser, heartbeat berhenti dan
     * scheduler (users:mark-stale-offline) akan menandainya offline.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'last_seen_at' => $user->last_seen_at,
        ]);
    }
}
