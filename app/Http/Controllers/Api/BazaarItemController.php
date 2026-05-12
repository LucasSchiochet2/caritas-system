<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BazaarItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BazaarItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeDioceseAdmin($request);

        $items = BazaarItem::query()
            ->orderBy('name')
            ->get()
            ->map(fn (BazaarItem $item) => $this->payload($item));

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeDioceseAdmin($request);

        $data = $request->validate([
            'suggested_price' => ['required', 'numeric', 'min:0'],
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'max:50'],
            'quantity' => ['required', 'integer', 'min:0'],
            'condition' => ['required', 'string', 'max:100'],
        ]);

        $item = BazaarItem::query()->create($data);

        return response()->json(['data' => $this->payload($item)], 201);
    }

    public function update(Request $request, BazaarItem $bazaarItem): JsonResponse
    {
        $this->authorizeDioceseAdmin($request);

        $data = $request->validate([
            'suggested_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'max:50'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'condition' => ['sometimes', 'required', 'string', 'max:100'],
        ]);

        $bazaarItem->fill($data);
        $bazaarItem->save();

        return response()->json(['data' => $this->payload($bazaarItem)]);
    }

    public function destroy(Request $request, BazaarItem $bazaarItem): JsonResponse
    {
        $this->authorizeDioceseAdmin($request);

        $bazaarItem->delete();

        return response()->json(null, 204);
    }

    private function authorizeDioceseAdmin(Request $request): void
    {
        abort_unless($request->user()->isDioceseAdmin() && $request->user()->tokenCan('diocese'), 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(BazaarItem $item): array
    {
        return [
            'id' => $item->id,
            'suggested_price' => $item->suggested_price,
            'name' => $item->name,
            'color' => $item->color,
            'size' => $item->size,
            'gender' => $item->gender,
            'quantity' => $item->quantity,
            'condition' => $item->condition,
        ];
    }
}