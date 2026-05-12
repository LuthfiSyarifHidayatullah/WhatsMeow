<?php

namespace App\Http\Controllers;

use App\Models\BotResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BotResponseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BotResponse::with('service');

        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $responses = $query->orderByDesc('priority')->get();

        return response()->json($responses);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'trigger_keyword' => 'required|string|max:255',
            'response_text' => 'required|string',
            'service_id' => 'nullable|exists:services,id',
            'match_type' => 'in:exact,contains,regex',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ]);

        $response = BotResponse::create($request->all());

        return response()->json($response, 201);
    }

    public function update(Request $request, BotResponse $botResponse): JsonResponse
    {
        $request->validate([
            'trigger_keyword' => 'string|max:255',
            'response_text' => 'string',
            'service_id' => 'nullable|exists:services,id',
            'match_type' => 'in:exact,contains,regex',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ]);

        $botResponse->update($request->all());

        return response()->json($botResponse);
    }

    public function destroy(BotResponse $botResponse): JsonResponse
    {
        $botResponse->delete();
        return response()->json(['message' => 'Bot response deleted']);
    }
}
