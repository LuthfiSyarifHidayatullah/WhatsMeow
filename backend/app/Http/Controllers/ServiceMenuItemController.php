<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceMenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceMenuItemController extends Controller
{
    /**
     * List sub-menu items. Optionally filtered by service_id.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ServiceMenuItem::with('service')
            ->orderBy('service_id')
            ->orderBy('position');

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);

        // Jika posisi tidak dikirim, letakkan di akhir.
        if (!isset($data['position'])) {
            $data['position'] = (int) ServiceMenuItem::where('service_id', $data['service_id'])->max('position') + 1;
        }

        $this->assertPositionAvailable($data['service_id'], $data['position']);

        $item = ServiceMenuItem::create($data);

        return response()->json($item->load('service'), 201);
    }

    public function show(ServiceMenuItem $serviceMenuItem): JsonResponse
    {
        return response()->json($serviceMenuItem->load('service'));
    }

    public function update(Request $request, ServiceMenuItem $serviceMenuItem): JsonResponse
    {
        $data = $this->validateData($request, $serviceMenuItem);

        $serviceId = $data['service_id'] ?? $serviceMenuItem->service_id;
        $position = $data['position'] ?? $serviceMenuItem->position;
        $this->assertPositionAvailable($serviceId, $position, $serviceMenuItem->id);

        $serviceMenuItem->update($data);

        return response()->json($serviceMenuItem->load('service'));
    }

    public function destroy(ServiceMenuItem $serviceMenuItem): JsonResponse
    {
        $serviceMenuItem->delete();
        return response()->json(['message' => 'Menu item deleted']);
    }

    /**
     * Validate request payload. On update, all fields are optional.
     */
    private function validateData(Request $request, ?ServiceMenuItem $existing = null): array
    {
        $required = $existing ? 'sometimes|required' : 'required';

        return $request->validate([
            'service_id' => [$existing ? 'sometimes' : 'required', 'exists:services,id'],
            'position' => ['nullable', 'integer', 'min:1'],
            'label' => [$required, 'string', 'max:255'],
            'action' => [$existing ? 'sometimes' : 'required', Rule::in(ServiceMenuItem::ACTIONS)],
            'response_text' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ]);
    }

    /**
     * Guard the unique (service_id, position) pair with a clear message
     * instead of a raw DB constraint error.
     */
    private function assertPositionAvailable(int $serviceId, int $position, ?int $ignoreId = null): void
    {
        $exists = ServiceMenuItem::where('service_id', $serviceId)
            ->where('position', $position)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            abort(response()->json([
                'message' => "Nomor pilihan {$position} sudah dipakai pada layanan ini. Gunakan nomor lain.",
            ], 422));
        }
    }
}
