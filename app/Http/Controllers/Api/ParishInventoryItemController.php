<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParishInventory;
use App\Models\ParishInventoryItem;
use App\Models\ParishInventoryItemQuantity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParishInventoryItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $parishInventoryScopeId = $this->parishInventoryScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
        }
        $items = ParishInventoryItem::query()
            ->with('quantities')
            ->when($parishScopeId !== null, fn ($query) => $query->whereHas('inventory', fn ($query) => $query->where('parish_id', $parishScopeId)))
            ->when($parishInventoryScopeId !== null, fn ($query) => $query->where('parish_inventory_id', $parishInventoryScopeId))
            ->get()
            ->map(fn (ParishInventoryItem $item) => $this->payload($item));

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
            'parish_inventory_id' => 'required|exists:parish_inventories,id',
            'name' => 'required|min:3|max:255',
            'description' => 'nullable|string|max:255',
        ]);
        if ($parishScopeId !== null) {
            abort_unless(
                ParishInventory::query()
                    ->where('id', $data['parish_inventory_id'])
                    ->where('parish_id', $parishScopeId)
                    ->exists(),
                403
            );
        }

        $itemQuantityData = $request->validate([
            'quantity' => 'required|integer|min:0',
            'valid_until' => 'required|date|after_or_equal:today',
        ]);
        if (array_key_exists('quantity', $itemQuantityData)) {
            abort_unless($itemQuantityData['quantity'] >= 0, 422);
            $data['total_quantity'] = $itemQuantityData['quantity'];
        }

        $item = ParishInventoryItem::query()->create($data);

        

        if (array_key_exists('quantity', $itemQuantityData) && array_key_exists('valid_until', $itemQuantityData)) {
            ParishInventoryItemQuantity::query()->create([
                'parish_inventory_item_id' => $item->id,
                'quantity' => $itemQuantityData['quantity'],
                'valid_until' => $itemQuantityData['valid_until'],
            ]);
        }

        $item->load('quantities');

        return response()->json(['data' => $this->payload($item)], 201);
    }

    public function addQuantity(Request $request, ParishInventoryItem $parishInventoryItem): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
            abort_unless($parishInventoryItem->inventory?->parish_id === $parishScopeId, 403);
        }
        $data = $request->validate([
            'quantity' => 'required|integer|min:0',
            'valid_until' => 'required|date|after_or_equal:today',
        ]);

        ParishInventoryItemQuantity::query()->create([
            'parish_inventory_item_id' => $parishInventoryItem->id,
            'quantity' => $data['quantity'],
            'valid_until' => $data['valid_until'],
        ]);

        $parishInventoryItem->increment('total_quantity', $data['quantity']);
        $parishInventoryItem->refresh()->load('quantities');

        return response()->json(['data' => $this->payload($parishInventoryItem)]);
    }

    public function update(Request $request, ParishInventoryItem $parishInventoryItem): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
            abort_unless($parishInventoryItem->inventory?->parish_id === $parishScopeId, 403);
        }
        $data = $request->validate([
            'name' => 'required|min:3|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $parishInventoryItem->fill($data);
        $parishInventoryItem->save();
        $parishInventoryItem->load('quantities');

        return response()->json(['data' => $this->payload($parishInventoryItem)]);
    }

    public function destroy(Request $request, ParishInventoryItem $parishInventoryItem): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
            abort_unless($parishInventoryItem->inventory?->parish_id === $parishScopeId, 403);
        }

        $parishInventoryItem->delete();

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
    private function parishInventoryScopeId(Request $request): ?int
    {
        $itemId = $request->query('parish_inventory_id');
        if ($itemId !== null) {
            return (int) $itemId;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(ParishInventoryItem $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'parish_inventory_id' => $item->parish_inventory_id,
            'total_quantity' => $item->total_quantity,
            'quantities' => $item->quantities
                ->map(fn (ParishInventoryItemQuantity $quantity) => [
                    'id' => $quantity->id,
                    'quantity' => $quantity->quantity,
                    'valid_until' => $quantity->valid_until,
                    'created_at' => $quantity->created_at?->toIso8601String(),
                    'updated_at' => $quantity->updated_at?->toIso8601String(),
                ])
                ->values(),
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
        ];
    }
}
