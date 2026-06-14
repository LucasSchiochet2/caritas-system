<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParishInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParishInventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
        }
        $items = ParishInventory::query()
            ->when($parishScopeId !== null, fn ($query) => $query->where('parish_id', $parishScopeId))
            ->get()
            ->map(fn (ParishInventory $item) => $this->payload($item));

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
        }

        $data = $request->validate([
            'parish_id' => [$isDioceseScope ? 'required' : 'prohibited', 'integer', 'exists:parishes,id'],
            'name' => 'required|min:3|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $data['parish_id'] = $parishScopeId ?? $data['parish_id'];

        $item = ParishInventory::query()->create($data);

        return response()->json(['data' => $this->payload($item)], 201);
    }

    public function update(Request $request, ParishInventory $parishInventory): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
            abort_unless($parishInventory->parish_id === $parishScopeId, 403);
        }
        $data = $request->validate([
            'name' => 'required|min:3|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $parishInventory->fill($data);
        $parishInventory->save();

        return response()->json(['data' => $this->payload($parishInventory)]);
    }

    public function destroy(Request $request, ParishInventory $parishInventory): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
            abort_unless($parishInventory->parish_id === $parishScopeId, 403);
        }

        $parishInventory->delete();

        return response()->json(null, 204);
    }

    private function isDioceseScope(Request $request): bool
    {
        return $request->user()->isDioceseAdmin() && $request->user()->tokenCan('diocese');
    }

    private function parishScopeId(Request $request): ?int
    {
        $token = $request->user()->currentAccessToken();

        foreach ($token?->abilities ?? [] as $ability) {
            if (str_starts_with($ability, 'parish:')) {
                return (int) str($ability)->after('parish:')->toString();
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(ParishInventory $item): array
    {
        return [
            'id' => $item->id,
            'parish_id' => $item->parish_id,
            'name' => $item->name,
            'description' => $item->description,
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
        ];
    }
}
