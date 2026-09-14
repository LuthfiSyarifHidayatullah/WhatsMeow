<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpdController extends Controller
{
    public function index(): JsonResponse
    {
        $opds = Opd::withCount('services')
            ->orderBy('sort_order')
            ->get();

        return response()->json($opds);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:opds,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $opd = Opd::create($request->all());

        return response()->json($opd, 201);
    }

    public function show(Opd $opd): JsonResponse
    {
        return response()->json($opd->load('services'));
    }

    public function update(Request $request, Opd $opd): JsonResponse
    {
        $request->validate([
            'name' => 'string|max:255',
            'code' => 'string|max:50|unique:opds,code,' . $opd->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $opd->update($request->all());

        return response()->json($opd);
    }

    public function destroy(Opd $opd): JsonResponse
    {
        $opd->delete();
        return response()->json(['message' => 'OPD deleted']);
    }
}
