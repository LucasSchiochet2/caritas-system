<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BasketDelivery;
use App\Models\BasketDeliveryItem;
use App\Models\BasketTemplate;
use App\Models\Family;
use App\Models\ParishInventoryItem;
use App\Models\ParishInventoryItemQuantity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BasketDeliveryController extends Controller
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

        $deliveries = BasketDelivery::query()
            ->with($this->relations())
            ->when($parishScopeId !== null, fn ($query) => $query->where('parish_id', $parishScopeId))
            ->when($request->query('family_id') !== null, fn ($query) => $query->where('family_id', (int) $request->query('family_id')))
            ->latest('delivered_at')
            ->latest()
            ->get()
            ->map(fn (BasketDelivery $delivery) => $this->payload($delivery));

        return response()->json(['data' => $deliveries]);
    }

    public function familyIndex(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamily($request, $family);

        $deliveries = BasketDelivery::query()
            ->with($this->relations())
            ->where('family_id', $family->id)
            ->latest('delivered_at')
            ->latest()
            ->get()
            ->map(fn (BasketDelivery $delivery) => $this->payload($delivery));

        return response()->json(['data' => $deliveries]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'family_id' => ['required', 'integer', 'exists:families,id'],
            'basket_template_id' => ['nullable', 'integer', 'exists:basket_templates,id'],
            'delivered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required_without:basket_template_id', 'array', 'min:1'],
            'items.*.parish_inventory_item_id' => ['nullable', 'integer', 'exists:parish_inventory_items,id'],
            'items.*.parish_inventory_item_quantity_id' => ['nullable', 'integer', 'exists:parish_inventory_item_quantities,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $family = Family::query()->findOrFail($data['family_id']);
        $this->authorizeFamily($request, $family);

        $template = null;
        if (! empty($data['basket_template_id'])) {
            $template = BasketTemplate::query()
                ->with('items')
                ->findOrFail($data['basket_template_id']);

            abort_unless($template->parish_id === $family->parish_id, 403);
            abort_unless($template->active, 422);
        }

        $delivery = DB::transaction(function () use ($request, $data, $family, $template) {
            $requestedItems = $this->requestedItems($data, $template);
            $deliveryItems = $this->allocateDeliveryItems($requestedItems, $family->parish_id);

            $delivery = BasketDelivery::query()->create([
                'parish_id' => $family->parish_id,
                'family_id' => $family->id,
                'basket_template_id' => $template?->id,
                'created_by' => $request->user()->id,
                'delivered_at' => $data['delivered_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($deliveryItems as $item) {
                /** @var ParishInventoryItemQuantity $quantityRow */
                $quantityRow = $item['quantity_row'];
                $delivery->items()->create([
                    'parish_inventory_item_id' => $quantityRow->parish_inventory_item_id,
                    'parish_inventory_item_quantity_id' => $quantityRow->id,
                    'quantity' => $item['quantity'],
                ]);

                $quantityRow->decrement('quantity', $item['quantity']);
                $quantityRow->parishInventoryItem->decrement('total_quantity', $item['quantity']);
            }

            return $delivery;
        });

        $delivery->load($this->relations());

        return response()->json(['data' => $this->payload($delivery)], 201);
    }

    /**
     * @return array<int, array{parish_inventory_item_id?: int, parish_inventory_item_quantity_id?: int, quantity: int}>
     */
    private function requestedItems(array $data, ?BasketTemplate $template): array
    {
        if (array_key_exists('items', $data)) {
            foreach ($data['items'] as $item) {
                if (empty($item['parish_inventory_item_id']) && empty($item['parish_inventory_item_quantity_id'])) {
                    throw ValidationException::withMessages([
                        'items' => ['Informe parish_inventory_item_id ou parish_inventory_item_quantity_id.'],
                    ]);
                }
            }

            return $data['items'];
        }

        return $template?->items
            ->map(fn ($item) => [
                'parish_inventory_item_id' => $item->parish_inventory_item_id,
                'quantity' => $item->quantity,
            ])
            ->values()
            ->all() ?? [];
    }

    /**
     * @param array<int, array{parish_inventory_item_id?: int, parish_inventory_item_quantity_id?: int, quantity: int}> $requestedItems
     * @return array<int, array{quantity_row: ParishInventoryItemQuantity, quantity: int}>
     */
    private function allocateDeliveryItems(array $requestedItems, int $parishId): array
    {
        $deliveryItems = [];
        $reservedByQuantityId = [];

        foreach ($requestedItems as $item) {
            if (! empty($item['parish_inventory_item_quantity_id'])) {
                $deliveryItems[] = $this->allocateSelectedLot($item, $parishId, $reservedByQuantityId);
                continue;
            }

            array_push($deliveryItems, ...$this->allocateByNearestValidity($item, $parishId, $reservedByQuantityId));
        }

        return $deliveryItems;
    }

    /**
     * @param array{parish_inventory_item_id?: int, parish_inventory_item_quantity_id?: int, quantity: int} $item
     * @return array{quantity_row: ParishInventoryItemQuantity, quantity: int}
     */
    private function allocateSelectedLot(array $item, int $parishId, array &$reservedByQuantityId): array
    {
        $quantityRow = ParishInventoryItemQuantity::query()
            ->with('parishInventoryItem.inventory')
            ->whereKey($item['parish_inventory_item_quantity_id'])
            ->lockForUpdate()
            ->first();

        abort_unless($quantityRow !== null, 422);
        abort_unless($quantityRow->parishInventoryItem?->inventory?->parish_id === $parishId, 403);

        if (! empty($item['parish_inventory_item_id'])) {
            abort_unless((int) $item['parish_inventory_item_id'] === $quantityRow->parish_inventory_item_id, 422);
        }

        $availableQuantity = $quantityRow->quantity - ($reservedByQuantityId[$quantityRow->id] ?? 0);

        if ($availableQuantity < $item['quantity']) {
            throw ValidationException::withMessages([
                'items' => ['Quantidade insuficiente para o lote selecionado.'],
            ]);
        }

        $reservedByQuantityId[$quantityRow->id] = ($reservedByQuantityId[$quantityRow->id] ?? 0) + $item['quantity'];

        return [
            'quantity_row' => $quantityRow,
            'quantity' => $item['quantity'],
        ];
    }

    /**
     * @param array{parish_inventory_item_id?: int, quantity: int} $item
     * @return array<int, array{quantity_row: ParishInventoryItemQuantity, quantity: int}>
     */
    private function allocateByNearestValidity(array $item, int $parishId, array &$reservedByQuantityId): array
    {
        if (empty($item['parish_inventory_item_id'])) {
            throw ValidationException::withMessages([
                'items' => ['Informe parish_inventory_item_id quando nao selecionar um lote especifico.'],
            ]);
        }

        $inventoryItem = ParishInventoryItem::query()
            ->with('inventory')
            ->find($item['parish_inventory_item_id']);

        abort_unless($inventoryItem !== null, 422);
        abort_unless($inventoryItem->inventory?->parish_id === $parishId, 403);

        $remaining = $item['quantity'];
        $allocations = [];
        $quantityRows = ParishInventoryItemQuantity::query()
            ->where('parish_inventory_item_id', $inventoryItem->id)
            ->where('quantity', '>', 0)
            ->orderBy('valid_until')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($quantityRows as $quantityRow) {
            if ($remaining <= 0) {
                break;
            }

            $availableQuantity = $quantityRow->quantity - ($reservedByQuantityId[$quantityRow->id] ?? 0);
            if ($availableQuantity <= 0) {
                continue;
            }

            $quantity = min($remaining, $availableQuantity);
            $allocations[] = [
                'quantity_row' => $quantityRow->setRelation('parishInventoryItem', $inventoryItem),
                'quantity' => $quantity,
            ];
            $reservedByQuantityId[$quantityRow->id] = ($reservedByQuantityId[$quantityRow->id] ?? 0) + $quantity;
            $remaining -= $quantity;
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'items' => ['Quantidade insuficiente para o item selecionado.'],
            ]);
        }

        return $allocations;
    }

    public function show(Request $request, BasketDelivery $basketDelivery): JsonResponse
    {
        $this->authorizeParish($request, $basketDelivery->parish_id);

        $basketDelivery->load($this->relations());

        return response()->json(['data' => $this->payload($basketDelivery)]);
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'family',
            'template',
            'items.inventoryItem',
            'items.inventoryItemQuantity',
        ];
    }

    private function authorizeFamily(Request $request, Family $family): void
    {
        $this->authorizeParish($request, $family->parish_id);
    }

    private function authorizeParish(Request $request, int $parishId): void
    {
        if ($this->isDioceseScope($request)) {
            return;
        }

        $parishScopeId = $this->parishScopeId($request);

        abort_unless(
            $parishScopeId !== null
                && $parishScopeId === $parishId
                && $request->user()->canManageParish($parishScopeId),
            403
        );
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
    private function payload(BasketDelivery $delivery): array
    {
        return [
            'id' => $delivery->id,
            'parish_id' => $delivery->parish_id,
            'family_id' => $delivery->family_id,
            'family_name' => $delivery->family?->name,
            'basket_template_id' => $delivery->basket_template_id,
            'basket_template_name' => $delivery->template?->name,
            'delivered_at' => $delivery->delivered_at?->toIso8601String(),
            'notes' => $delivery->notes,
            'items' => $delivery->items
                ->map(fn (BasketDeliveryItem $item) => [
                    'id' => $item->id,
                    'parish_inventory_item_id' => $item->parish_inventory_item_id,
                    'parish_inventory_item_quantity_id' => $item->parish_inventory_item_quantity_id,
                    'name' => $item->inventoryItem?->name,
                    'quantity' => $item->quantity,
                    'valid_until' => $item->inventoryItemQuantity?->valid_until,
                ])
                ->values(),
            'created_at' => $delivery->created_at?->toIso8601String(),
            'updated_at' => $delivery->updated_at?->toIso8601String(),
        ];
    }
}
