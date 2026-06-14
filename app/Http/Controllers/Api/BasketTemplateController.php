<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BasketTemplate;
use App\Models\BasketTemplateItem;
use App\Models\ParishInventoryItem;
use App\Models\ParishInventoryItemQuantity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BasketTemplateController extends Controller
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

        $templates = BasketTemplate::query()
            ->with($this->relations())
            ->when($parishScopeId !== null, fn ($query) => $query->where('parish_id', $parishScopeId))
            ->orderBy('name')
            ->get()
            ->map(fn (BasketTemplate $template) => $this->payload($template));

        return response()->json(['data' => $templates]);
    }

    public function show(Request $request, BasketTemplate $basketTemplate): JsonResponse
    {
        $this->authorizeTemplate($request, $basketTemplate);

        $basketTemplate->load($this->relations());

        return response()->json(['data' => $this->payload($basketTemplate)]);
    }

    public function store(Request $request): JsonResponse
    {
        $parishId = $this->writableParishId($request);

        $data = $request->validate([
            'parish_id' => [$this->isDioceseScope($request) ? 'required' : 'sometimes', 'integer', 'exists:parishes,id'],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.parish_inventory_item_id' => ['required', 'integer', 'distinct', 'exists:parish_inventory_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $this->assertInventoryItemsBelongToParish(collect($data['items'])->pluck('parish_inventory_item_id')->all(), $parishId);

        $template = DB::transaction(function () use ($data, $parishId) {
            $template = BasketTemplate::query()->create([
                'parish_id' => $parishId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'active' => $data['active'] ?? true,
            ]);

            foreach ($data['items'] as $item) {
                $template->items()->create([
                    'parish_inventory_item_id' => $item['parish_inventory_item_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return $template;
        });

        $template->load($this->relations());

        return response()->json(['data' => $this->payload($template)], 201);
    }

    public function update(Request $request, BasketTemplate $basketTemplate): JsonResponse
    {
        $this->authorizeTemplate($request, $basketTemplate);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.parish_inventory_item_id' => ['required_with:items', 'integer', 'distinct', 'exists:parish_inventory_items,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
        ]);

        if (array_key_exists('items', $data)) {
            $this->assertInventoryItemsBelongToParish(collect($data['items'])->pluck('parish_inventory_item_id')->all(), $basketTemplate->parish_id);
        }

        DB::transaction(function () use ($basketTemplate, $data) {
            $basketTemplate->fill([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'active' => $data['active'] ?? $basketTemplate->active,
            ]);
            $basketTemplate->save();

            if (array_key_exists('items', $data)) {
                $basketTemplate->items()->delete();
                foreach ($data['items'] as $item) {
                    $basketTemplate->items()->create([
                        'parish_inventory_item_id' => $item['parish_inventory_item_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }
        });

        $basketTemplate->load($this->relations());

        return response()->json(['data' => $this->payload($basketTemplate)]);
    }

    public function destroy(Request $request, BasketTemplate $basketTemplate): JsonResponse
    {
        $this->authorizeTemplate($request, $basketTemplate);

        $basketTemplate->delete();

        return response()->json(null, 204);
    }

    private function writableParishId(Request $request): int
    {
        if ($this->isDioceseScope($request)) {
            return (int) $request->input('parish_id');
        }

        $parishScopeId = $this->parishScopeId($request);

        abort_unless($parishScopeId !== null && $request->user()->canManageParish($parishScopeId), 403);
        abort_if($request->has('parish_id') && (int) $request->input('parish_id') !== $parishScopeId, 403);

        return $parishScopeId;
    }

    private function authorizeTemplate(Request $request, BasketTemplate $basketTemplate): void
    {
        if ($this->isDioceseScope($request)) {
            return;
        }

        $parishScopeId = $this->parishScopeId($request);

        abort_unless(
            $parishScopeId !== null
                && $basketTemplate->parish_id === $parishScopeId
                && $request->user()->canManageParish($parishScopeId),
            403
        );
    }

    /**
     * @param array<int, int> $itemIds
     */
    private function assertInventoryItemsBelongToParish(array $itemIds, int $parishId): void
    {
        $validCount = ParishInventoryItem::query()
            ->whereIn('id', $itemIds)
            ->whereHas('inventory', fn ($query) => $query->where('parish_id', $parishId))
            ->count();

        abort_unless($validCount === count($itemIds), 403);
    }

    private function isDioceseScope(Request $request): bool
    {
        return $request->user()->isDioceseAdmin() && $request->user()->tokenCan('diocese');
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'items.inventoryItem.quantities' => fn ($query) => $query
                ->where('quantity', '>', 0)
                ->orderBy('valid_until')
                ->orderBy('id'),
        ];
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
    private function payload(BasketTemplate $template): array
    {
        return [
            'id' => $template->id,
            'parish_id' => $template->parish_id,
            'name' => $template->name,
            'description' => $template->description,
            'active' => $template->active,
            'items' => $template->items
                ->map(fn (BasketTemplateItem $item) => [
                    'id' => $item->id,
                    'parish_inventory_item_id' => $item->parish_inventory_item_id,
                    'name' => $item->inventoryItem?->name,
                    'quantity' => $item->quantity,
                    'available_total_quantity' => $item->inventoryItem?->quantities->sum('quantity') ?? 0,
                    'quantities' => $item->inventoryItem?->quantities
                        ->map(fn (ParishInventoryItemQuantity $quantity) => [
                            'id' => $quantity->id,
                            'quantity' => $quantity->quantity,
                            'valid_until' => $quantity->valid_until,
                        ])
                        ->values() ?? [],
                ])
                ->values(),
            'created_at' => $template->created_at?->toIso8601String(),
            'updated_at' => $template->updated_at?->toIso8601String(),
        ];
    }
}
