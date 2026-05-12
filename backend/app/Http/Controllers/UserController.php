<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with('service');

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $users = $query->get();

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,officer,supervisor',
            'service_id' => 'nullable|exists:services,id',
            'max_concurrent_chats' => 'integer|min:1|max:20',
        ]);

        $user = User::create([
            ...$request->except('password'),
            'password' => Hash::make($request->password),
        ]);

        return response()->json($user->load('service'), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->load('service'));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $user->id,
            'role' => 'in:admin,officer,supervisor',
            'service_id' => 'nullable|exists:services,id',
            'is_available' => 'boolean',
            'max_concurrent_chats' => 'integer|min:1|max:20',
        ]);

        $data = $request->except('password');
        if ($request->has('password') && $request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json($user->load('service'));
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    /**
     * Toggle officer availability
     */
    public function toggleAvailability(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['is_available' => !$user->is_available]);

        return response()->json([
            'is_available' => $user->is_available,
            'message' => $user->is_available ? 'Status: Tersedia' : 'Status: Tidak Tersedia',
        ]);
    }
}
