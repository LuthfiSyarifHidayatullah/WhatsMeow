<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $services = Service::withCount('officers')
            ->orderBy('sort_order')
            ->get();

        return response()->json($services);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:services,code',
            'description' => 'nullable|string',
            'keywords' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $service = Service::create($request->all());

        return response()->json($service, 201);
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json($service->load('officers'));
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $request->validate([
            'name' => 'string|max:255',
            'code' => 'string|max:50|unique:services,code,' . $service->id,
            'description' => 'nullable|string',
            'keywords' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $service->update($request->all());

        return response()->json($service);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();
        return response()->json(['message' => 'Service deleted']);
    }
}
