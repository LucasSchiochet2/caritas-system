<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParishInventory;
use App\Models\ParishInventoryItem;
use App\Models\ParishInventoryItemQuantity;
use App\Models\ParishInventoryRepasse;
use App\Models\ParishInventoryRepasseItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParishInventoryRepasseController extends Controller
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

        $requestedParishId = $request->query('parish_id');
        abort_if($requestedParishId !== null && ! $isDioceseScope && (int) $requestedParishId !== $parishScopeId, 403);

        $repasses = ParishInventoryRepasse::query()
            ->with($this->relations())
            ->when($isDioceseScope && $requestedParishId !== null, fn ($query) => $query->where('parish_id', (int) $requestedParishId))
            ->when(! $isDioceseScope, fn ($query) => $query->where('parish_id', $parishScopeId))
            ->latest('delivered_at')
            ->latest()
            ->get()
            ->map(fn (ParishInventoryRepasse $repasse) => $this->payload($repasse));

        return response()->json(['data' => $repasses]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($this->isDioceseScope($request), 403);

        $data = $request->validate([
            'parish_id' => ['required', 'integer', 'exists:parishes,id'],
            'delivered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'min:2', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.valid_until' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $repasse = DB::transaction(function () use ($request, $data): ParishInventoryRepasse {
            $repasse = ParishInventoryRepasse::query()->create([
                'parish_id' => $data['parish_id'],
                'created_by' => $request->user()->id,
                'movement_type' => 'out',
                'delivered_at' => $data['delivered_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $repasse->items()->create($item);
                $this->addItemToParishInventory((int) $data['parish_id'], $item);
            }

            return $repasse;
        });

        $repasse->load($this->relations());

        return response()->json(['data' => $this->payload($repasse)], 201);
    }

    public function show(Request $request, ParishInventoryRepasse $parishInventoryRepasse): JsonResponse
    {
        $this->authorizeRepasse($request, $parishInventoryRepasse);

        $parishInventoryRepasse->load($this->relations());

        return response()->json(['data' => $this->payload($parishInventoryRepasse)]);
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'parish:id,name,slug',
            'creator:id,name,email',
            'items',
        ];
    }

    private function authorizeRepasse(Request $request, ParishInventoryRepasse $repasse): void
    {
        if ($this->isDioceseScope($request)) {
            return;
        }

        $parishScopeId = $this->parishScopeId($request);

        abort_unless(
            $parishScopeId !== null
                && $repasse->parish_id === $parishScopeId
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
     * @param  array{name: string, description?: string|null, quantity: int, valid_until: string}  $item
     */
    private function addItemToParishInventory(int $parishId, array $item): void
    {
        $inventory = ParishInventory::query()->firstOrCreate(
            ['parish_id' => $parishId],
            [
                'name' => 'Inventario Principal',
                'description' => 'Criado automaticamente a partir de repasse.',
            ],
        );

        $inventoryItem = ParishInventoryItem::query()->firstOrCreate(
            [
                'parish_inventory_id' => $inventory->id,
                'name' => $item['name'],
            ],
            [
                'description' => $item['description'] ?? null,
                'total_quantity' => 0,
            ],
        );

        ParishInventoryItemQuantity::query()->create([
            'parish_inventory_item_id' => $inventoryItem->id,
            'quantity' => $item['quantity'],
            'valid_until' => $item['valid_until'],
        ]);

        $inventoryItem->increment('total_quantity', $item['quantity']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(ParishInventoryRepasse $repasse): array
    {
        return [
            'id' => $repasse->id,
            'parish_id' => $repasse->parish_id,
            'parish_name' => $repasse->parish?->name,
            'created_by' => $repasse->created_by,
            'created_by_name' => $repasse->creator?->name,
            'movement_type' => $repasse->movement_type,
            'delivered_at' => $repasse->delivered_at?->toIso8601String(),
            'notes' => $repasse->notes,
            'items' => $repasse->items
                ->map(fn (ParishInventoryRepasseItem $item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'valid_until' => $item->valid_until?->toDateString(),
                    'created_at' => $item->created_at?->toIso8601String(),
                    'updated_at' => $item->updated_at?->toIso8601String(),
                ])
                ->values(),
            'created_at' => $repasse->created_at?->toIso8601String(),
            'updated_at' => $repasse->updated_at?->toIso8601String(),
        ];
    }
}
